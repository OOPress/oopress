<?php

declare(strict_types=1);

namespace OOPress\Log\Controller;

use Doctrine\DBAL\Connection;
use OOPress\Api\Controller\ApiController;
use OOPress\Security\AuthorizationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * LogController — Admin log viewer.
 * 
 * @internal
 */
class LogController extends ApiController
{
    private const LOGS_TABLE = 'oop_logs';
    
    public function __construct(
        private readonly Connection $connection,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * GET /admin/logs
     * Log viewer page.
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'view_logs')) {
            return $this->error('Access denied', 403);
        }
        
        $content = $this->renderTemplate('admin/logs/index.html.twig', [
            'levels' => $this->getLogLevels(),
            'channels' => $this->getChannels(),
        ]);
        
        return new Response($content);
    }
    
    /**
     * GET /api/v1/logs
     * Get logs via API.
     */
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'view_logs')) {
            return $this->error('Access denied', 403);
        }
        
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 50);
        $level = $request->query->get('level');
        $channel = $request->query->get('channel');
        $search = $request->query->get('search');
        
        $offset = ($page - 1) * $limit;
        
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from(self::LOGS_TABLE, 'l')
            ->orderBy('created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        
        if ($level) {
            $qb->andWhere('level = :level')->setParameter('level', $level);
        }
        
        if ($channel) {
            $qb->andWhere('channel = :channel')->setParameter('channel', $channel);
        }
        
        if ($search) {
            $qb->andWhere('message LIKE :search')->setParameter('search', '%' . $search . '%');
        }
        
        $logs = $qb->executeQuery()->fetchAllAssociative();
        
        // Count total
        $countQb = clone $qb;
        $countQb->select('COUNT(*)')->setFirstResult(0)->setMaxResults(null);
        $total = (int) $countQb->executeQuery()->fetchOne();
        
        return $this->success($logs, null, [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
            'filters' => [
                'level' => $level,
                'channel' => $channel,
                'search' => $search,
            ],
        ]);
    }
    
    /**
     * GET /api/v1/logs/stats
     * Get log statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'view_logs')) {
            return $this->error('Access denied', 403);
        }
        
        // Get counts by level
        $levelStats = $this->connection->fetchAllAssociative(
            'SELECT level, COUNT(*) as count FROM ' . self::LOGS_TABLE . ' GROUP BY level'
        );
        
        // Get counts by channel
        $channelStats = $this->connection->fetchAllAssociative(
            'SELECT channel, COUNT(*) as count FROM ' . self::LOGS_TABLE . ' GROUP BY channel'
        );
        
        // Get counts by day (last 7 days)
        $dailyStats = $this->connection->fetchAllAssociative(
            'SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM ' . self::LOGS_TABLE . ' 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date DESC'
        );
        
        // Get total size
        $totalSize = $this->getLogFileSize();
        
        return $this->success([
            'total' => $this->getTotalLogCount(),
            'by_level' => $levelStats,
            'by_channel' => $channelStats,
            'by_day' => $dailyStats,
            'file_size' => $totalSize,
            'formatted_file_size' => $this->formatBytes($totalSize),
        ]);
    }
    
    /**
     * DELETE /api/v1/logs
     * Clear logs (admin only).
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'clear_logs')) {
            return $this->error('Access denied', 403);
        }
        
        try {
            $this->connection->executeStatement('TRUNCATE TABLE ' . self::LOGS_TABLE);
            return $this->success(null, 'Logs cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to clear logs: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * GET /admin/logs/download
     * Download logs as CSV.
     */
    public function download(Request $request): Response
    {
        $user = $this->getUser($request);
        
        if (!$this->authorization->isGranted($user, 'view_logs')) {
            return $this->error('Access denied', 403);
        }
        
        $logs = $this->connection->fetchAllAssociative(
            'SELECT * FROM ' . self::LOGS_TABLE . ' ORDER BY created_at DESC LIMIT 10000'
        );
        
        $csv = fopen('php://temp', 'r+');
        
        // Add headers
        if (!empty($logs)) {
            fputcsv($csv, array_keys($logs[0]));
            
            // Add data
            foreach ($logs as $log) {
                fputcsv($csv, $log);
            }
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="oopress-logs-' . date('Y-m-d') . '.csv"',
            ]
        );
    }
    
    private function getTotalLogCount(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . self::LOGS_TABLE);
    }
    
    private function getLogFileSize(): int
    {
        $logPath = __DIR__ . '/../../../var/logs/oopress.log';
        
        if (file_exists($logPath)) {
            return filesize($logPath);
        }
        
        return 0;
    }
    
    private function getLogLevels(): array
    {
        return [
            'debug' => 'Debug',
            'info' => 'Info',
            'notice' => 'Notice',
            'warning' => 'Warning',
            'error' => 'Error',
            'critical' => 'Critical',
            'alert' => 'Alert',
            'emergency' => 'Emergency',
        ];
    }
    
    private function getChannels(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT DISTINCT channel FROM ' . self::LOGS_TABLE
        );
        
        return array_column($rows, 'channel');
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use TemplateManager
        return '<h1>Log Viewer</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}