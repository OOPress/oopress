<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Resolver;

use OOPress\Media\MediaManager;
use OOPress\Media\MediaRepository;
use OOPress\Security\AuthorizationManager;

/**
 * MediaResolver — Resolves media-related GraphQL fields.
 * 
 * @internal
 */
class MediaResolver
{
    public function __construct(
        private readonly MediaManager $mediaManager,
        private readonly MediaRepository $mediaRepository,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * Resolve single media.
     */
    public function resolveMedia($root, array $args, array $context): ?\OOPress\Media\MediaFile
    {
        $user = $context['user'] ?? null;
        
        $media = $this->mediaRepository->find((int) $args['id']);
        
        if (!$media) {
            return null;
        }
        
        if (!$this->authorization->isGranted($user, 'view', $media)) {
            return null;
        }
        
        return $media;
    }
    
    /**
     * Resolve media list.
     */
    public function resolveMediaList($root, array $args, array $context): array
    {
        $user = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($user, 'view_media_library')) {
            return [];
        }
        
        $type = $args['type'] ?? null;
        
        if ($type) {
            return $this->mediaRepository->findByType($type, $args['limit'], $args['offset']);
        }
        
        // Get all media (simplified - would need pagination)
        return [];
    }
    
    /**
     * Resolve upload media mutation.
     */
    public function resolveUploadMedia($root, array $args, array $context): ?\OOPress\Media\MediaFile
    {
        $user = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($user, 'upload_media')) {
            throw new \RuntimeException('Access denied');
        }
        
        // This would handle base64 uploads
        // For now, return null
        
        return null;
    }
    
    /**
     * Resolve delete media mutation.
     */
    public function resolveDeleteMedia($root, array $args, array $context): bool
    {
        $user = $context['user'] ?? null;
        $media = $this->mediaRepository->find((int) $args['id']);
        
        if (!$media) {
            return false;
        }
        
        if (!$this->authorization->isGranted($user, 'delete', $media)) {
            throw new \RuntimeException('Access denied');
        }
        
        return $this->mediaManager->delete($media, $user);
    }
}