<?php

declare(strict_types=1);

namespace OOPress\Security\Voter;

use OOPress\Security\UserInterface;

/**
 * VoterInterface — Contract for authorization voters.
 * 
 * @api
 */
interface VoterInterface
{
    public const ACCESS_GRANTED = 1;
    public const ACCESS_DENIED = -1;
    public const ACCESS_ABSTAIN = 0;
    
    /**
     * Check if this voter supports the given attribute and subject.
     */
    public function supports(string $attribute, mixed $subject): bool;
    
    /**
     * Vote on the given attribute and subject.
     */
    public function vote(UserInterface $user, string $attribute, mixed $subject): int;
}