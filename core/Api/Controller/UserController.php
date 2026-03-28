<?php

declare(strict_types=1);

namespace OOPress\Api\Controller;

use Doctrine\DBAL\Connection;
use OOPress\Security\User;
use OOPress\Security\UserProvider;
use OOPress\Security\PasswordHasher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * UserController — User API endpoints.
 * 
 * @api
 */
class UserController extends ApiController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UserProvider $userProvider,
        private readonly PasswordHasher $passwordHasher,
    ) {}
    
    /**
     * GET /api/v1/users
     * List users (admin only).
     */
    public function list(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!$this->isAdmin($user)) {
            return $this->error('Access denied', 403);
        }
        
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $offset = ($page - 1) * $limit;
        
        $users = $this->connection->fetchAllAssociative(
            'SELECT id, username, email, roles, status, created_at, updated_at 
             FROM oop_users 
             LIMIT :limit OFFSET :offset',
            ['limit' => $limit, 'offset' => $offset]
        );
        
        $total = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM oop_users');
        
        return $this->success($users, null, [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }
    
    /**
     * GET /api/v1/users/{id}
     * Get a single user.
     */
    public function get(string $id, Request $request): JsonResponse
    {
        $currentUser = $this->requireAuth($request);
        
        $user = $this->userProvider->loadUserById((int) $id);
        
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        // Users can only view their own profile unless admin
        if (!$this->isAdmin($currentUser) && $currentUser->getId() !== $user->getId()) {
            return $this->error('Access denied', 403);
        }
        
        return $this->success($this->serializeUser($user));
    }
    
    /**
     * GET /api/v1/users/me
     * Get current authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        return $this->success($this->serializeUser($user));
    }
    
    /**
     * POST /api/v1/users
     * Create a new user (admin only).
     */
    public function create(Request $request): JsonResponse
    {
        $currentUser = $this->requireAuth($request);
        
        if (!$this->isAdmin($currentUser)) {
            return $this->error('Access denied', 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        if (!empty($errors)) {
            return $this->error('Validation failed', 422, $errors);
        }
        
        // Check if user exists
        $exists = $this->connection->fetchOne(
            'SELECT id FROM oop_users WHERE username = :username OR email = :email',
            ['username' => $data['username'], 'email' => $data['email']]
        );
        
        if ($exists) {
            return $this->error('User already exists', 422);
        }
        
        // Hash password using standalone service
        try {
            $hashedPassword = $this->passwordHasher->hash($data['password']);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
        
        // Create user
        $this->connection->insert('oop_users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'roles' => json_encode($data['roles'] ?? ['ROLE_USER']),
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $userId = $this->connection->lastInsertId();
        
        return $this->created(['id' => $userId], 'User created successfully');
    }
    
    /**
     * PUT /api/v1/users/{id}
     * Update a user.
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $currentUser = $this->requireAuth($request);
        $targetId = (int) $id;
        
        $user = $this->userProvider->loadUserById($targetId);
        
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        // Users can only update their own profile unless admin
        if (!$this->isAdmin($currentUser) && $currentUser->getId() !== $targetId) {
            return $this->error('Access denied', 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateData = [];
        
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        
        if (isset($data['password'])) {
            try {
                // Validate password strength
                $strengthErrors = $this->passwordHasher->validateStrength($data['password'], [
                    'min_length' => 8,
                    'require_uppercase' => true,
                    'require_lowercase' => true,
                    'require_number' => true,
                ]);
                
                if (!empty($strengthErrors)) {
                    return $this->error('Password validation failed', 422, $strengthErrors);
                }
                
                $updateData['password'] = $this->passwordHasher->hash($data['password']);
            } catch (\InvalidArgumentException $e) {
                return $this->error($e->getMessage(), 422);
            }
        }
        
        if ($this->isAdmin($currentUser)) {
            if (isset($data['roles'])) {
                $updateData['roles'] = json_encode($data['roles']);
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->connection->update('oop_users', $updateData, ['id' => $targetId]);
        }
        
        return $this->success(null, 'User updated successfully');
    }
    
    /**
     * DELETE /api/v1/users/{id}
     * Delete a user (admin only).
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        $currentUser = $this->requireAuth($request);
        
        if (!$this->isAdmin($currentUser)) {
            return $this->error('Access denied', 403);
        }
        
        $targetId = (int) $id;
        
        // Prevent deleting yourself
        if ($currentUser->getId() === $targetId) {
            return $this->error('Cannot delete your own account', 422);
        }
        
        $affected = $this->connection->delete('oop_users', ['id' => $targetId]);
        
        if ($affected === 0) {
            return $this->error('User not found', 404);
        }
        
        return $this->noContent();
    }
    
    private function serializeUser(\OOPress\Security\UserInterface $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'status' => $user->getStatus(),
            'created_at' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'urls' => [
                'self' => "/api/v1/users/{$user->getId()}",
            ],
        ];
    }
    
    private function isAdmin(\OOPress\Security\UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}