<?php

declare(strict_types=1);

namespace OOPress\Api\Controller;

use OOPress\Security\UserProvider;
use OOPress\Security\PasswordHasher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AuthController — Authentication API endpoints.
 * 
 * @api
 */
class AuthController extends ApiController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly PasswordHasher $passwordHasher,
    ) {}
    
    /**
     * POST /api/v1/auth/login
     * Authenticate user and start session.
     */
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $errors = $this->validate($data, [
            'username' => 'required',
            'password' => 'required',
        ]);
        
        if (!empty($errors)) {
            return $this->error('Validation failed', 422, $errors);
        }
        
        try {
            $user = $this->userProvider->loadUserByUsername($data['username']);
        } catch (\Exception $e) {
            return $this->error('Invalid credentials', 401);
        }
        
        if (!$user->isActive()) {
            return $this->error('Account is disabled', 401);
        }
        
        // Verify password using standalone service
        if (!$this->passwordHasher->verify($data['password'], $user->getPassword())) {
            return $this->error('Invalid credentials', 401);
        }
        
        // Check if password needs rehash (algorithm or cost changed)
        if ($this->passwordHasher->needsRehash($user->getPassword())) {
            $newHash = $this->passwordHasher->hash($data['password']);
            $this->updateUserPassword($user->getId(), $newHash);
        }
        
        // Start session
        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->set('username', $user->getUsername());
        $session->set('user_roles', $user->getRoles());
        
        return $this->success([
            'user' => $this->serializeUser($user),
            'session' => [
                'id' => $session->getId(),
                'expires' => $session->getMetadataBag()->getCreated(),
            ],
        ], 'Login successful');
    }
    
    /**
     * POST /api/v1/auth/logout
     * Logout current user.
     */
    public function logout(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->invalidate();
        
        return $this->success(null, 'Logout successful');
    }
    
    /**
     * GET /api/v1/auth/me
     * Get current authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return $this->error('Not authenticated', 401);
        }
        
        $user = $this->userProvider->loadUserById($userId);
        
        if (!$user) {
            $session->invalidate();
            return $this->error('User not found', 401);
        }
        
        return $this->success($this->serializeUser($user));
    }
    
    /**
     * POST /api/v1/auth/register
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        if (!empty($errors)) {
            return $this->error('Validation failed', 422, $errors);
        }
        
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
        
        // Check if user exists
        $exists = $this->checkUserExists($data['username'], $data['email']);
        
        if ($exists) {
            return $this->error('User already exists', 422);
        }
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hash($data['password']);
        
        // Create user
        $userId = $this->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            ['ROLE_USER']
        );
        
        // Auto-login
        $session = $request->getSession();
        $session->set('user_id', $userId);
        $session->set('username', $data['username']);
        $session->set('user_roles', ['ROLE_USER']);
        
        return $this->success([
            'user_id' => $userId,
            'username' => $data['username'],
        ], 'Registration successful');
    }
    
    private function serializeUser(\OOPress\Security\UserInterface $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
    
    private function checkUserExists(string $username, string $email): bool
    {
        // This would be injected or accessed via repository
        // Placeholder for now
        return false;
    }
    
    private function createUser(string $username, string $email, string $hashedPassword, array $roles): int
    {
        // This would be injected or accessed via repository
        // Placeholder for now
        return 1;
    }
    
    private function updateUserPassword(int $userId, string $newHash): void
    {
        // This would be injected or accessed via repository
        // Placeholder for now
    }
}