<?php

declare(strict_types=1);

namespace OOPress\Log\Monitor;

use OOPress\Log\Logger;

/**
 * PerformanceMonitor — Tracks performance metrics.
 * 
 * @api
 */
class PerformanceMonitor
{
    private array $timers = [];
    private array $metrics = [];
    
    public function __construct(
        private readonly Logger $logger,
    ) {}
    
    /**
     * Start a timer.
     */
    public function start(string $name): void
    {
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(),
        ];
    }
    
    /**
     * Stop a timer and record metric.
     */
    public function stop(string $name, array $tags = []): float
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }
        
        $duration = microtime(true) - $this->timers[$name]['start'];
        $memory = memory_get_usage() - $this->timers[$name]['memory_start'];
        
        $this->metrics[] = [
            'name' => $name,
            'duration' => $duration,
            'memory' => $memory,
            'tags' => $tags,
            'timestamp' => time(),
        ];
        
        // Log slow operations (> 1 second)
        if ($duration > 1.0) {
            $this->logger->warning('Slow operation detected', [
                'operation' => $name,
                'duration' => round($duration, 3),
                'memory' => $this->formatBytes($memory),
                'tags' => $tags,
            ]);
        }
        
        unset($this->timers[$name]);
        
        return $duration;
    }
    
    /**
     * Measure a callable.
     */
    public function measure(string $name, callable $callable, array $tags = []): mixed
    {
        $this->start($name);
        $result = $callable();
        $this->stop($name, $tags);
        return $result;
    }
    
    /**
     * Get all metrics.
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
    
    /**
     * Get metrics summary.
     */
    public function getSummary(): array
    {
        $summary = [];
        
        foreach ($this->metrics as $metric) {
            $name = $metric['name'];
            if (!isset($summary[$name])) {
                $summary[$name] = [
                    'count' => 0,
                    'total_duration' => 0,
                    'max_duration' => 0,
                    'min_duration' => PHP_FLOAT_MAX,
                    'avg_duration' => 0,
                    'total_memory' => 0,
                ];
            }
            
            $summary[$name]['count']++;
            $summary[$name]['total_duration'] += $metric['duration'];
            $summary[$name]['max_duration'] = max($summary[$name]['max_duration'], $metric['duration']);
            $summary[$name]['min_duration'] = min($summary[$name]['min_duration'], $metric['duration']);
            $summary[$name]['total_memory'] += $metric['memory'];
        }
        
        // Calculate averages
        foreach ($summary as &$stats) {
            $stats['avg_duration'] = $stats['total_duration'] / $stats['count'];
            $stats['avg_memory'] = $stats['total_memory'] / $stats['count'];
        }
        
        return $summary;
    }
    
    /**
     * Log performance summary.
     */
    public function logSummary(): void
    {
        $summary = $this->getSummary();
        
        foreach ($summary as $name => $stats) {
            $this->logger->info('Performance metric', [
                'metric' => $name,
                'count' => $stats['count'],
                'avg_duration' => round($stats['avg_duration'], 3),
                'max_duration' => round($stats['max_duration'], 3),
                'avg_memory' => $this->formatBytes($stats['avg_memory']),
            ]);
        }
    }
    
    /**
     * Format bytes to human-readable.
     */
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
}