<?php

declare(strict_types=1);

namespace OOPress\Search\Controller;

use OOPress\Search\SearchManager;
use OOPress\Search\SearchQuery;
use OOPress\Api\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * SearchController — Search endpoints.
 * 
 * @api
 */
class SearchController extends ApiController
{
    public function __construct(
        private readonly SearchManager $searchManager,
    ) {}
    
    /**
     * GET /search
     * Search page.
     */
    public function search(Request $request): Response
    {
        $query = SearchQuery::fromRequest($request->query->all());
        
        if ($user = $this->getUser($request)) {
            $query->setUserRoles($user->getRoles());
            $query->setUserId($user->getId());
        } else {
            $query->setUserRoles(['anonymous']);
        }
        
        $results = $this->searchManager->search($query);
        
        $content = $this->renderTemplate('search/results.html.twig', [
            'query' => $query->getKeyword(),
            'results' => $results,
            'total' => $results->getTotal(),
            'facets' => $results->getFacets(),
            'suggestions' => $results->getSuggestions(),
            'page' => ($query->getOffset() / $query->getLimit()) + 1,
            'limit' => $query->getLimit(),
        ]);
        
        return new Response($content);
    }
    
    /**
     * GET /api/v1/search
     * Search API endpoint.
     */
    public function searchApi(Request $request): JsonResponse
    {
        $query = SearchQuery::fromRequest($request->query->all());
        
        if ($user = $this->getUser($request)) {
            $query->setUserRoles($user->getRoles());
            $query->setUserId($user->getId());
        } else {
            $query->setUserRoles(['anonymous']);
        }
        
        if (!$this->searchManager->isAvailable()) {
            return $this->error('Search service unavailable', 503);
        }
        
        $results = $this->searchManager->search($query);
        
        return $this->success($results->toArray(), null, [
            'query' => $query->getKeyword(),
            'page' => ($query->getOffset() / $query->getLimit()) + 1,
            'limit' => $query->getLimit(),
        ]);
    }
    
    /**
     * POST /api/v1/search/index
     * Index a document (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->error('Access denied', 403);
        }
        
        // This would accept document data and index it
        // Implementation depends on indexable providers
        
        return $this->success(null, 'Document indexed');
    }
    
    /**
     * POST /api/v1/search/rebuild
     * Rebuild search index (admin only).
     */
    public function rebuild(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->error('Access denied', 403);
        }
        
        // Rebuild in background
        $this->searchManager->rebuild();
        
        return $this->success(null, 'Index rebuild started');
    }
    
    /**
     * GET /api/v1/search/stats
     * Get search statistics (admin only).
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->error('Access denied', 403);
        }
        
        $stats = $this->searchManager->getStats();
        
        return $this->success($stats);
    }
    
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use TemplateManager
        return '<h1>Search Results</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}