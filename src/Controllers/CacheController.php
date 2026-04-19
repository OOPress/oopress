<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\Cache\CacheManager;
use OOPress\Core\Cache\PageCache;
use OOPress\Core\Cache\QueryCache;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class CacheController
{
    private Engine $view;
    private CacheManager $cache;
    private PageCache $pageCache;
    private QueryCache $queryCache;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
        $this->cache = new CacheManager();
        $this->pageCache = new PageCache($this->cache);
        $this->queryCache = new QueryCache($this->cache);
    }
    
    private function checkAdminAccess(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function index(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $cacheStats = $this->getCacheStats();
        
        $content = $this->view->render('admin/cache/index', [
            'title' => __('Cache'),
            'stats' => $cacheStats,
            'page_cache_enabled' => \OOPress\Models\Setting::get('page_cache_enabled', false),
            'query_cache_enabled' => \OOPress\Models\Setting::get('query_cache_enabled', true),
            'cache_ttl' => \OOPress\Models\Setting::get('cache_ttl', 3600)
        ]);
        
        return new Response($content);
    }
    
    public function clear(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $type = $request->input('type', 'all');
        
        switch ($type) {
            case 'page':
                $this->pageCache->clear();
                $_SESSION['flash_success'] = __('Page cache cleared');
                break;
            case 'query':
                $this->queryCache->clear();
                $_SESSION['flash_success'] = __('Query cache cleared');
                break;
            case 'all':
                $this->cache->clear();
                $_SESSION['flash_success'] = __('All cache cleared');
                break;
        }
        
        return Response::redirect('/admin/cache');
    }
    
    private function getCacheStats(): array
    {
        $files = glob($this->cache->getCachePath() . '*.cache');
        $totalSize = 0;
        $fileCount = count($files);
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'files' => $fileCount,
            'size' => $this->formatSize($totalSize),
            'path' => $this->cache->getCachePath()
        ];
    }
        
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}