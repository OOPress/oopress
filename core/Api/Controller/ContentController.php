<?php

declare(strict_types=1);

namespace OOPress\Api\Controller;

use OOPress\Content\ContentRepository;
use OOPress\Content\ContentTypeManager;
use OOPress\Content\Query\ContentQuery;
use OOPress\Security\AuthorizationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ContentController — Content API endpoints.
 * 
 * @api
 */
class ContentController extends ApiController
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly ContentTypeManager $contentTypeManager,
        private readonly ContentQuery $contentQuery,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * GET /api/v1/content
     * List all content with pagination and filtering.
     */
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $type = $request->query->get('type');
        $language = $request->query->get('language', 'en');
        $status = $request->query->get('status');
        
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        $query = $this->contentQuery
            ->language($language)
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        
        if ($type) {
            $query->type($type);
        }
        
        if ($status) {
            $query->status($status);
        }
        
        $content = $query->execute();
        $total = $query->count();
        
        $data = [];
        foreach ($content as $item) {
            $translation = $item->getTranslation($language);
            if ($translation) {
                $data[] = $this->serializeContent($item, $translation);
            }
        }
        
        return $this->success($data, null, [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }
    
    /**
     * GET /api/v1/content/{id}
     * Get a single content item.
     */
    public function get(string $id, Request $request): JsonResponse
    {
        $language = $request->query->get('language', 'en');
        
        $content = $this->contentRepository->find((int) $id, $language);
        
        if (!$content) {
            return $this->error('Content not found', 404);
        }
        
        $translation = $content->getTranslation($language);
        
        if (!$translation) {
            return $this->error('Translation not found', 404);
        }
        
        // Check authorization
        $user = $this->getUser($request);
        if (!$this->authorization->isGranted($user, 'view', $content)) {
            return $this->error('Access denied', 403);
        }
        
        return $this->success($this->serializeContent($content, $translation));
    }
    
    /**
     * GET /api/v1/content/{id}/translations
     * Get all translations of a content item.
     */
    public function getTranslations(string $id, Request $request): JsonResponse
    {
        $content = $this->contentRepository->find((int) $id);
        
        if (!$content) {
            return $this->error('Content not found', 404);
        }
        
        $user = $this->getUser($request);
        if (!$this->authorization->isGranted($user, 'view', $content)) {
            return $this->error('Access denied', 403);
        }
        
        $translations = [];
        foreach ($content->getTranslations() as $language => $translation) {
            $translations[$language] = [
                'language' => $translation->language,
                'title' => $translation->title,
                'slug' => $translation->slug,
                'summary' => $translation->summary,
                'is_default' => $translation->isDefault,
                'url' => "/api/v1/content/{$id}/translate/{$language}",
            ];
        }
        
        return $this->success($translations);
    }
    
    /**
     * POST /api/v1/content
     * Create new content.
     */
    public function create(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        $errors = $this->validate($data, [
            'content_type' => 'required',
            'title' => 'required|min:3|max:255',
            'language' => 'required',
        ]);
        
        if (!empty($errors)) {
            return $this->error('Validation failed', 422, $errors);
        }
        
        // Check content type exists
        $contentType = $this->contentTypeManager->getContentType($data['content_type']);
        if (!$contentType) {
            return $this->error('Invalid content type', 422);
        }
        
        // Create content (simplified - will be expanded)
        // This would call a ContentManager service to create the content
        
        return $this->created(null, 'Content created successfully');
    }
    
    /**
     * PUT /api/v1/content/{id}
     * Update existing content.
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        $content = $this->contentRepository->find((int) $id);
        
        if (!$content) {
            return $this->error('Content not found', 404);
        }
        
        if (!$this->authorization->isGranted($user, 'edit', $content)) {
            return $this->error('Access denied', 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        // Update content logic would go here
        
        return $this->success(null, 'Content updated successfully');
    }
    
    /**
     * DELETE /api/v1/content/{id}
     * Delete content.
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        $content = $this->contentRepository->find((int) $id);
        
        if (!$content) {
            return $this->error('Content not found', 404);
        }
        
        if (!$this->authorization->isGranted($user, 'delete', $content)) {
            return $this->error('Access denied', 403);
        }
        
        $this->contentRepository->delete($content);
        
        return $this->noContent();
    }
    
    /**
     * POST /api/v1/content/{id}/publish
     * Publish content.
     */
    public function publish(string $id, Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        $content = $this->contentRepository->find((int) $id);
        
        if (!$content) {
            return $this->error('Content not found', 404);
        }
        
        if (!$this->authorization->isGranted($user, 'publish', $content)) {
            return $this->error('Access denied', 403);
        }
        
        // Publish logic would go here
        
        return $this->success(null, 'Content published successfully');
    }
    
    /**
     * Serialize content for API response.
     */
    private function serializeContent(\OOPress\Content\Content $content, \OOPress\Content\ContentTranslation $translation): array
    {
        return [
            'id' => $content->id,
            'type' => $content->contentType,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'summary' => $translation->summary,
            'body' => $translation->body,
            'language' => $translation->language,
            'status' => $content->status,
            'author_id' => $content->authorId,
            'created_at' => $content->createdAt->format(\DateTimeInterface::ATOM),
            'updated_at' => $content->updatedAt->format(\DateTimeInterface::ATOM),
            'published_at' => $content->publishedAt?->format(\DateTimeInterface::ATOM),
            'fields' => $translation->fields,
            'urls' => [
                'self' => "/api/v1/content/{$content->id}",
                'translations' => "/api/v1/content/{$content->id}/translations",
            ],
        ];
    }
}