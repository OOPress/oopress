<?php

declare(strict_types=1);

namespace OOPress\Core;

use OOPress\Core\Version;

class Debug
{
    /**
     * Get system debug information including version
     */
    public static function getSystemInfo(): array
    {
        return [
            'oopress' => Version::getInfo(),
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            ],
            'database' => [
                'type' => $_ENV['DB_TYPE'] ?? 'Not configured',
                'host' => $_ENV['DB_HOST'] ?? 'Not configured',
                'name' => $_ENV['DB_NAME'] ?? 'Not configured',
            ],
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'fileinfo' => extension_loaded('fileinfo'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'zip' => extension_loaded('zip'),
            ],
            'paths' => [
                'root' => dirname(__DIR__, 2),
                'public' => dirname(__DIR__, 2) . '/public',
                'themes' => dirname(__DIR__, 2) . '/themes',
                'plugins' => dirname(__DIR__, 2) . '/plugins',
                'storage' => dirname(__DIR__, 2) . '/storage',
            ]
        ];
    }
    
    /**
     * Get debug information as formatted HTML
     */
    public static function getSystemInfoHtml(): string
    {
        $info = self::getSystemInfo();
        $html = '<div class="debug-info">';
        
        $html .= '<h3>OOPress Debug Information</h3>';
        
        // OOPress Info
        $html .= '<div class="debug-section">';
        $html .= '<h4>Application</h4>';
        $html .= '<table>';
        foreach ($info['oopress'] as $key => $value) {
            $html .= sprintf('<tr><td>%s</td><td>%s</td></tr>', ucfirst($key), htmlspecialchars($value));
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // PHP Info
        $html .= '<div class="debug-section">';
        $html .= '<h4>PHP Configuration</h4>';
        $html .= '<table>';
        foreach ($info['php'] as $key => $value) {
            $html .= sprintf('<tr><td>%s</td><td>%s</td></tr>', ucfirst(str_replace('_', ' ', $key)), htmlspecialchars($value));
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Server Info
        $html .= '<div class="debug-section">';
        $html .= '<h4>Server Information</h4>';
        $html .= '<table>';
        foreach ($info['server'] as $key => $value) {
            $html .= sprintf('<tr><td>%s</td><td>%s</td></tr>', ucfirst(str_replace('_', ' ', $key)), htmlspecialchars($value));
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Database Info
        $html .= '<div class="debug-section">';
        $html .= '<h4>Database Configuration</h4>';
        $html .= '<table>';
        foreach ($info['database'] as $key => $value) {
            $html .= sprintf('<tr><td>%s</td><td>%s</td></tr>', ucfirst($key), htmlspecialchars($value));
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Extensions
        $html .= '<div class="debug-section">';
        $html .= '<h4>PHP Extensions</h4>';
        $html .= '<table>';
        foreach ($info['extensions'] as $ext => $loaded) {
            $status = $loaded ? '✓ Loaded' : '✗ Not loaded';
            $class = $loaded ? 'success' : 'error';
            $html .= sprintf('<tr><td>%s</td><td class="%s">%s</td></tr>', $ext, $class, $status);
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Paths
        $html .= '<div class="debug-section">';
        $html .= '<h4>System Paths</h4>';
        $html .= '<table>';
        foreach ($info['paths'] as $key => $path) {
            $exists = is_dir($path) || file_exists($path);
            $status = $exists ? '✓ Exists' : '✗ Missing';
            $class = $exists ? 'success' : 'error';
            $html .= sprintf('<tr><td>%s</td><td class="%s">%s</td><td>%s</td></tr>', ucfirst($key), $class, $status, htmlspecialchars($path));
        }
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Add CSS
        $html .= '<style>
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .debug-info h3 {
            color: #FF8C00;
            margin-top: 0;
            border-bottom: 2px solid #FF8C00;
            padding-bottom: 10px;
        }
        .debug-section {
            margin-bottom: 20px;
        }
        .debug-section h4 {
            color: #495057;
            margin-bottom: 10px;
        }
        .debug-info table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .debug-info td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .debug-info td:first-child {
            font-weight: bold;
            background: #e9ecef;
            width: 30%;
        }
        .debug-info .success {
            color: #28a745;
            font-weight: bold;
        }
        .debug-info .error {
            color: #dc3545;
            font-weight: bold;
        }
        </style>';
        
        return $html;
    }
}
