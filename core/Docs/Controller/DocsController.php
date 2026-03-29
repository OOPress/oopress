<?php

declare(strict_types=1);

namespace OOPress\Docs\Controller;

use OOPress\Api\Controller\ApiController;
use OOPress\Docs\DocGenerator;
use OOPress\Security\AuthorizationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * DocsController — Documentation endpoints.
 * 
 * @api
 */
class DocsController extends ApiController
{
    public function __construct(
        private readonly DocGenerator $docGenerator,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * GET /docs
     * Documentation index page.
     */
    public function index(Request $request): Response
    {
        $content = file_get_contents(__DIR__ . '/../../../public/docs/index.html');
        
        if (!$content) {
            $content = '<h1>Documentation</h1><p>Documentation not found. Run <code>php oopress docs:generate</code> to generate documentation.</p>';
        }
        
        return new Response($content);
    }
    
    /**
     * GET /docs/api/{class}
     * API documentation page.
     */
    public function api(string $class, Request $request): Response
    {
        $file = __DIR__ . '/../../../public/docs/api/' . $class . '.html';
        
        if (!file_exists($file)) {
            return new Response('Documentation not found', 404);
        }
        
        $content = file_get_contents($file);
        return new Response($content);
    }
    
    /**
     * GET /docs/guides/{guide}
     * Guide page.
     */
    public function guide(string $guide, Request $request): Response
    {
        $file = __DIR__ . '/../../../public/docs/guides/' . $guide . '.html';
        
        if (!file_exists($file)) {
            return new Response('Guide not found', 404);
        }
        
        $content = file_get_contents($file);
        return new Response($content);
    }
    
    /**
     * POST /api/v1/docs/generate
     * Generate documentation (admin only).
     */
    public function generate(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!$this->authorization->isGranted($user, 'generate_docs')) {
            return $this->error('Access denied', 403);
        }
        
        $result = $this->docGenerator->generate();
        
        if ($result->isSuccess()) {
            return $this->success([
                'generated' => $result->getGeneratedCount(),
                'duration' => $result->getDuration(),
            ], 'Documentation generated successfully');
        }
        
        return $this->error('Documentation generation failed', 500, $result->getErrors());
    }
}