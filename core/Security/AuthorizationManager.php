<?php

declare(strict_types=1);

namespace OOPress\Security;

use OOPress\Security\Voter\VoterInterface;
use OOPress\Security\Voter\ContentVoter;

/**
 * AuthorizationManager — Manages authorization decisions.
 * 
 * @api
 */
class AuthorizationManager
{
    /**
     * @var array<VoterInterface>
     */
    private array $voters = [];
    
    public function __construct()
    {
        $this->registerCoreVoters();
    }
    
    private function registerCoreVoters(): void
    {
        $this->voters[] = new ContentVoter();
    }
    
    /**
     * Register a voter.
     */
    public function registerVoter(VoterInterface $voter): void
    {
        $this->voters[] = $voter;
    }
    
    /**
     * Check if a user is granted access.
     */
    public function isGranted(UserInterface $user, string $attribute, mixed $subject = null): bool
    {
        foreach ($this->voters as $voter) {
            if (!$voter->supports($attribute, $subject)) {
                continue;
            }
            
            $result = $voter->vote($user, $attribute, $subject);
            
            if ($result === VoterInterface::ACCESS_GRANTED) {
                return true;
            }
            
            if ($result === VoterInterface::ACCESS_DENIED) {
                return false;
            }
        }
        
        // If no voter voted, deny by default
        return false;
    }
    
    /**
     * Check if current user has a role.
     */
    public function hasRole(UserInterface $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }
    
    /**
     * Check if current user is admin.
     */
    public function isAdmin(UserInterface $user): bool
    {
        return $this->hasRole($user, 'ROLE_ADMIN');
    }
}