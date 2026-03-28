<?php

declare(strict_types=1);

namespace OOPress\Security\Voter;

use OOPress\Content\Content;
use OOPress\Security\UserInterface;

/**
 * ContentVoter — Handles content authorization.
 * 
 * @api
 */
class ContentVoter implements VoterInterface
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const PUBLISH = 'publish';
    
    public function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Content) {
            return false;
        }
        
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::PUBLISH], true);
    }
    
    public function vote(UserInterface $user, string $attribute, mixed $subject): int
    {
        $content = $subject;
        
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $content);
            case self::EDIT:
                return $this->canEdit($user, $content);
            case self::DELETE:
                return $this->canDelete($user, $content);
            case self::PUBLISH:
                return $this->canPublish($user, $content);
        }
        
        return self::ACCESS_ABSTAIN;
    }
    
    private function canView(UserInterface $user, Content $content): int
    {
        // Admin can view anything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return self::ACCESS_GRANTED;
        }
        
        // Published content is public
        if ($content->isPublished()) {
            return self::ACCESS_GRANTED;
        }
        
        // Authors can view their own drafts
        if ($content->authorId === $user->getId()) {
            return self::ACCESS_GRANTED;
        }
        
        return self::ACCESS_DENIED;
    }
    
    private function canEdit(UserInterface $user, Content $content): int
    {
        // Admin can edit anything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return self::ACCESS_GRANTED;
        }
        
        // Authors can edit their own content
        if ($content->authorId === $user->getId()) {
            return self::ACCESS_GRANTED;
        }
        
        return self::ACCESS_DENIED;
    }
    
    private function canDelete(UserInterface $user, Content $content): int
    {
        // Admin can delete anything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return self::ACCESS_GRANTED;
        }
        
        // Authors can delete their own unpublished content
        if ($content->authorId === $user->getId() && !$content->isPublished()) {
            return self::ACCESS_GRANTED;
        }
        
        return self::ACCESS_DENIED;
    }
    
    private function canPublish(UserInterface $user, Content $content): int
    {
        // Only users with publish permission
        if (in_array('ROLE_EDITOR', $user->getRoles(), true) || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return self::ACCESS_GRANTED;
        }
        
        return self::ACCESS_DENIED;
    }
}