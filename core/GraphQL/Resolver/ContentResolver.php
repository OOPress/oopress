<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Resolver;

use OOPress\Content\ContentRepository;
use OOPress\Content\ContentTypeManager;
use OOPress\Content\Query\ContentQuery;
use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;
use OOPress\Security\AuthorizationManager;
use OOPress\Security\UserInterface;

/**
 * ContentResolver — Resolves content-related GraphQL fields.
 * 
 * @internal
 */
class ContentResolver
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly ContentTypeManager $contentTypeManager,
        private readonly ContentQuery $contentQuery,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * Resolve single content.
     */
    public function resolveContent($root, array $args, array $context): ?Content
    {
        $user = $context['user'] ?? null;
        
        if (isset($args['id'])) {
            $content = $this->contentRepository->find((int) $args['id']);
        } elseif (isset($args['slug'])) {
            $language = $args['language'] ?? 'en';
            $content = $this->contentRepository->findBySlug($args['slug'], $language);
        } else {
            return null;
        }
        
        if (!$content) {
            return null;
        }
        
        if (!$this->authorization->isGranted($user, 'view', $content)) {
            return null;
        }
        
        return $content;
    }
    
    /**
     * Resolve multiple contents.
     */
    public function resolveContents($root, array $args, array $context): array
    {
        $user = $context['user'] ?? null;
        
        $query = $this->contentQuery
            ->limit($args['limit'])
            ->offset($args['offset'])
            ->orderBy($args['sortBy'], $args['sortOrder']);
        
        if (isset($args['type'])) {
            $query->type($args['type']);
        }
        
        if (isset($args['status'])) {
            $query->status($args['status']);
        }
        
        if (isset($args['language'])) {
            $query->language($args['language']);
        }
        
        $contents = $query->execute();
        
        // Filter by permission
        return array_filter($contents, function($content) use ($user) {
            return $this->authorization->isGranted($user, 'view', $content);
        });
    }
    
    /**
     * Resolve content search.
     */
    public function resolveSearch($root, array $args, array $context): array
    {
        // Will be implemented when search is integrated
        return [
            'total' => 0,
            'results' => [],
            'facets' => [],
        ];
    }
    
    /**
     * Resolve create content mutation.
     */
    public function resolveCreateContent($root, array $args, array $context): ?Content
    {
        $user = $context['user'] ?? null;
        
        if (!$user || !$this->authorization->isGranted($user, 'create', 'content')) {
            throw new \RuntimeException('Access denied');
        }
        
        $input = $args['input'];
        
        // Create content logic here
        // Returns new Content object
        
        return null;
    }
    
    /**
     * Resolve update content mutation.
     */
    public function resolveUpdateContent($root, array $args, array $context): ?Content
    {
        $user = $context['user'] ?? null;
        $content = $this->contentRepository->find((int) $args['id']);
        
        if (!$content) {
            throw new \RuntimeException('Content not found');
        }
        
        if (!$this->authorization->isGranted($user, 'edit', $content)) {
            throw new \RuntimeException('Access denied');
        }
        
        // Update content logic here
        
        return $content;
    }
    
    /**
     * Resolve delete content mutation.
     */
    public function resolveDeleteContent($root, array $args, array $context): bool
    {
        $user = $context['user'] ?? null;
        $content = $this->contentRepository->find((int) $args['id']);
        
        if (!$content) {
            return false;
        }
        
        if (!$this->authorization->isGranted($user, 'delete', $content)) {
            throw new \RuntimeException('Access denied');
        }
        
        $this->contentRepository->delete($content);
        
        return true;
    }
    
    /**
     * Resolve publish content mutation.
     */
    public function resolvePublishContent($root, array $args, array $context): ?Content
    {
        $user = $context['user'] ?? null;
        $content = $this->contentRepository->find((int) $args['id']);
        
        if (!$content) {
            throw new \RuntimeException('Content not found');
        }
        
        if (!$this->authorization->isGranted($user, 'publish', $content)) {
            throw new \RuntimeException('Access denied');
        }
        
        // Publish logic here
        
        return $content;
    }
}