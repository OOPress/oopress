<?php

declare(strict_types=1);

namespace OOPress\Media\Controller;

use OOPress\Media\MediaManager;
use OOPress\Media\MediaRepository;
use OOPress\Security\AuthorizationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * MediaController — Media management endpoints.
 * 
 * @internal
 */
class MediaController extends \OOPress\Api\Controller\ApiController
{
    public function __construct(
        private readonly MediaManager $mediaManager,
        private readonly MediaRepository $repository,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * GET /admin/media
     * Media library page.
     */
    public function library(Request $request): Response
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'access_media_library')) {
            return $this->error('Access denied', 403);
        }
        
        $page = (int) $request->query->get('page', 1);
        $limit = 24;
        $offset = ($page - 1) * $limit;
        
        $media = $this->repository->search($request->query->get('search', ''), $limit, $offset);
        $total = $this->repository->count();
        
        $content = $this->renderTemplate('admin/media/library.html.twig', [
            'media' => $media,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
        
        return new Response($content);
    }
    
    /**
     * POST /api/v1/media/upload
     * Upload a file.
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!$this->authorization->isGranted($user, 'upload_media')) {
            return $this->error('Access denied', 403);
        }
        
        $file = $request->files->get('file');
        
        if (!$file instanceof UploadedFile) {
            return $this->error('No file uploaded', 400);
        }
        
        $destination = $request->get('destination', 'public');
        $metadata = json_decode($request->get('metadata', '[]'), true);
        
        $media = $this->mediaManager->upload($file, $destination, $user->getId(), $metadata);
        
        if (!$media) {
            return $this->error('Upload failed', 400, $this->mediaManager->getErrors());
        }
        
        return $this->success([
            'id' => $media->id,
            'url' => $this->mediaManager->getUrl($media),
            'thumbnail' => $this->mediaManager->getUrl($media, 'thumbnail'),
            'filename' => $media->filename,
            'original_name' => $media->originalName,
            'size' => $media->getFormattedSize(),
            'type' => $media->mimeType,
        ], 'File uploaded successfully');
    }
    
    /**
     * GET /api/v1/media/{id}
     * Get media details.
     */
    public function get(string $id, Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        $media = $this->repository->find((int) $id);
        
        if (!$media) {
            return $this->error('Media not found', 404);
        }
        
        if (!$this->authorization->isGranted($user, 'view', $media)) {
            return $this->error('Access denied', 403);
        }
        
        return $this->success([
            'id' => $media->id,
            'filename' => $media->filename,
            'original_name' => $media->originalName,
            'url' => $this->mediaManager->getUrl($media),
            'thumbnail' => $this->mediaManager->getUrl($media, 'thumbnail'),
            'medium' => $this->mediaManager->getUrl($media, 'medium'),
            'large' => $this->mediaManager->getUrl($media, 'large'),
            'size' => $media->getFormattedSize(),
            'mime_type' => $media->mimeType,
            'extension' => $media->extension,
            'created_at' => $media->createdAt->format(\DateTimeInterface::ATOM),
            'metadata' => $media->metadata,
        ]);
    }
    
    /**
     * DELETE /api/v1/media/{id}
     * Delete media.
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        $media = $this->repository->find((int) $id);
        
        if (!$media) {
            return $this->error('Media not found', 404);
        }
        
        if (!$this->authorization->isGranted($user, 'delete', $media)) {
            return $this->error('Access denied', 403);
        }
        
        if ($this->mediaManager->delete($media, $user)) {
            $this->repository->delete($media);
            return $this->success(null, 'Media deleted successfully');
        }
        
        return $this->error('Failed to delete media', 400, $this->mediaManager->getErrors());
    }
    
    /**
     * GET /files/public/{path}
     * Serve public files.
     */
    public function servePublic(string $path, Request $request): BinaryFileResponse
    {
        $fullPath = $this->mediaManager->getPublicPath() . '/' . $path;
        
        if (!file_exists($fullPath)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
        
        return new BinaryFileResponse($fullPath);
    }
    
    /**
     * GET /media/private/{id}
     * Serve private files (with access control).
     */
    public function servePrivate(string $id, Request $request): BinaryFileResponse
    {
        $user = $this->getUser($request);
        $media = $this->repository->find((int) $id);
        
        if (!$media) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
        
        if (!$this->authorization->isGranted($user, 'view', $media)) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
        
        $fullPath = $this->mediaManager->getPrivatePath() . '/' . $media->path;
        
        if (!file_exists($fullPath)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
        
        return new BinaryFileResponse($fullPath);
    }
    
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use TemplateManager
        return '<h1>Media Library</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}