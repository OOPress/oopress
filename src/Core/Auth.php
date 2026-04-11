<?php

declare(strict_types=1);

namespace OOPress\Core;

use OOPress\Models\User;

class Auth
{
    private Session $session;
    private ?User $user = null;
    
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->loadUserFromSession();
    }
    
    private function loadUserFromSession(): void
    {
        $userId = $this->session->get('user_id');
        if ($userId) {
            $this->user = User::find($userId);
            if (!$this->user || $this->user->status !== 'active') {
                $this->logout();
            }
        }
    }
    
    public function attempt(string $username, string $password): bool
    {
        // Try by username first
        $user = User::firstWhere(['username' => $username, 'status' => 'active']);
        
        // If not found, try by email
        if (!$user) {
            $user = User::firstWhere(['email' => $username, 'status' => 'active']);
        }
        
        if ($user && $user->verifyPassword($password)) {
            $this->session->set('user_id', $user->id);
            $this->user = $user;
            $user->updateLastLogin();
            return true;
        }
        
        return false;
    }
    
    public function logout(): void
    {
        $this->session->remove('user_id');
        $this->user = null;
    }
    
    public function check(): bool
    {
        return $this->user !== null;
    }
    
    public function user(): ?User
    {
        return $this->user;
    }
    
    public function id(): ?int
    {
        return $this->user ? $this->user->id : null;
    }
}