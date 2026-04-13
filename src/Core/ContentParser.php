<?php

declare(strict_types=1);

namespace OOPress\Core;

use Parsedown;
use OOPress\Models\Setting;

class ContentParser
{
    private array $allowedPhpFunctions = [
        'date', 'time', 'strtotime', 'number_format', 'round', 'ceil', 'floor',
        'ucfirst', 'strtolower', 'strtoupper', 'ucwords', 'trim', 'nl2br',
        'htmlspecialchars', 'strip_tags', 'substr', 'strlen', 'str_replace'
    ];
    
    private array $allowedPhpTags = [
        '<?php', '<?=', '?>'
    ];
    
    public function parse(string $content, string $format, array $data = []): string
    {
        return match($format) {
            'markdown' => $this->parseMarkdown($content),
            'php' => $this->parsePhp($content, $data),
            'html', 'tinymce' => $this->parseHtml($content),
            default => $content
        };
    }
    
    private function parseMarkdown(string $content): string
    {
        if (!class_exists('Parsedown')) {
            // Fallback to basic HTML if Parsedown not installed
            return nl2br(htmlspecialchars($content));
        }
        
        $parsedown = new Parsedown();
        return $parsedown->text($content);
    }
    
    private function parsePhp(string $content, array $data = []): string
    {
        // Extract variables for the template
        extract($data);
        
        // Security: Remove dangerous PHP functions
        $content = $this->sanitizePhp($content);
        
        // Capture output buffer
        ob_start();
        
        try {
            eval('?>' . $content);
            $output = ob_get_clean();
            return $output ?: '';
        } catch (\ParseError $e) {
            ob_end_clean();
            return '<div class="alert alert-error">PHP Parse Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        } catch (\Throwable $e) {
            ob_end_clean();
            return '<div class="alert alert-error">PHP Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    
    private function parseHtml(string $content): string
    {
        // Sanitize HTML to prevent XSS
        $allowedTags = Setting::get('allowed_html_tags', 
            '<p><br><b><strong><i><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><div><span><table><tr><td><th><thead><tbody><pre><code><blockquote><hr>'
        );
        
        return strip_tags($content, $allowedTags);
    }
    
    private function sanitizePhp(string $content): string
    {
        // Remove dangerous function calls
        $dangerous = ['exec', 'shell_exec', 'system', 'passthru', 'eval', 'assert', 
                      'popen', 'proc_open', 'curl_exec', 'file_get_contents', 'fopen',
                      'unlink', 'rmdir', 'mkdir', 'chmod', 'chown', 'ini_set'];
        
        foreach ($dangerous as $func) {
            $content = preg_replace('/' . preg_quote($func, '/') . '\s*\(/', '/* disabled */ ', $content);
        }
        
        return $content;
    }
    
    public function getFormatOptions(): array
    {
        return [
            'tinymce' => ['label' => 'TinyMCE (Rich Text)', 'icon' => '📝', 'description' => 'WYSIWYG editor with formatting toolbar'],
            'html' => ['label' => 'HTML', 'icon' => '🔧', 'description' => 'Write raw HTML code'],
            'markdown' => ['label' => 'Markdown', 'icon' => '📄', 'description' => 'Write using Markdown syntax'],
            'php' => ['label' => 'PHP (Advanced)', 'icon' => '⚙️', 'description' => 'Write PHP code (restricted functions)']
        ];
    }
}