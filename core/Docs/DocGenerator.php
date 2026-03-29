<?php

declare(strict_types=1);

namespace OOPress\Docs;

use OOPress\Path\PathResolver;
use OOPress\Event\HookDispatcher;

/**
 * DocGenerator — Generates documentation from code and markdown files.
 * 
 * GDPR compliant: All documentation stored locally.
 * 
 * @api
 */
class DocGenerator
{
    private array $config;
    private array $docs = [];
    private array $errors = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly HookDispatcher $hookDispatcher,
        array $config = [],
    ) {
        $this->config = array_merge([
            'docs_dir' => 'docs',
            'api_dir' => 'docs/api',
            'guides_dir' => 'docs/guides',
            'output_dir' => 'public/docs',
        ], $config);
    }
    
    /**
     * Generate all documentation.
     */
    public function generate(): DocResult
    {
        $result = new DocResult();
        
        // Generate API docs from code
        $this->generateApiDocs($result);
        
        // Generate user guides from markdown
        $this->generateGuides($result);
        
        // Generate index
        $this->generateIndex($result);
        
        // Dispatch event for modules to add documentation
        $event = new Event\DocGenerateEvent($this, $result);
        $this->hookDispatcher->dispatch($event, 'docs.generate');
        
        return $result;
    }
    
    /**
     * Generate API documentation from code.
     */
    private function generateApiDocs(DocResult $result): void
    {
        $apiOutputDir = $this->pathResolver->getPublicRoot() . '/' . $this->config['api_dir'];
        
        if (!is_dir($apiOutputDir)) {
            mkdir($apiOutputDir, 0755, true);
        }
        
        // Scan core directory for PHP files
        $coreDir = $this->pathResolver->getCorePath();
        $files = $this->scanPhpFiles($coreDir);
        
        $classes = [];
        
        foreach ($files as $file) {
            $classDocs = $this->parsePhpDoc($file);
            if ($classDocs) {
                $classes[] = $classDocs;
            }
        }
        
        // Generate API index
        $this->generateApiIndex($classes, $apiOutputDir);
        
        // Generate class pages
        foreach ($classes as $class) {
            $this->generateClassPage($class, $apiOutputDir);
            $result->addGenerated('api/' . $class['name'] . '.html');
        }
        
        $result->addGenerated('api/index.html');
    }
    
    /**
     * Scan PHP files recursively.
     */
    private function scanPhpFiles(string $dir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Parse PHP docblock for class documentation.
     */
    private function parsePhpDoc(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);
        $namespace = $namespaceMatch[1] ?? '';
        
        // Extract class/interface/trait
        preg_match('/(class|interface|trait)\s+([^\s{]+)/', $content, $classMatch);
        
        if (empty($classMatch)) {
            return null;
        }
        
        $type = $classMatch[1];
        $name = $classMatch[2];
        $fullName = $namespace . '\\' . $name;
        
        // Extract docblock
        preg_match('/\/\*\*(.*?)\*\//s', $content, $docMatch);
        $docblock = $docMatch[1] ?? '';
        
        // Parse annotations
        $description = $this->parseDescription($docblock);
        $tags = $this->parseTags($docblock);
        
        // Extract methods
        $methods = $this->parseMethods($content);
        
        // Extract properties
        $properties = $this->parseProperties($content);
        
        // Determine if API public
        $isApi = str_contains($docblock, '@api') || str_contains($filePath, '/Api/');
        $isInternal = str_contains($docblock, '@internal');
        
        return [
            'name' => $name,
            'full_name' => $fullName,
            'namespace' => $namespace,
            'type' => $type,
            'description' => $description,
            'tags' => $tags,
            'methods' => $methods,
            'properties' => $properties,
            'is_api' => $isApi,
            'is_internal' => $isInternal,
            'file' => $filePath,
        ];
    }
    
    /**
     * Parse description from docblock.
     */
    private function parseDescription(string $docblock): string
    {
        $lines = explode("\n", $docblock);
        $description = [];
        
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\s*\*\s?/', '', $line));
            if (str_starts_with($line, '@')) {
                break;
            }
            if (!empty($line)) {
                $description[] = $line;
            }
        }
        
        return implode(' ', $description);
    }
    
    /**
     * Parse tags from docblock.
     */
    private function parseTags(string $docblock): array
    {
        $tags = [];
        preg_match_all('/@(\w+)\s+([^\n]+)/', $docblock, $matches);
        
        foreach ($matches[1] as $i => $tagName) {
            $tags[$tagName][] = trim($matches[2][$i]);
        }
        
        return $tags;
    }
    
    /**
     * Parse methods from class content.
     */
    private function parseMethods(string $content): array
    {
        $methods = [];
        preg_match_all(
            '/(public|protected|private)\s+function\s+(\w+)\s*\(([^)]*)\)/',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $visibility = $match[1];
            $name = $match[2];
            $params = $match[3];
            
            // Extract method docblock
            preg_match('/\/\*\*(.*?)\*\/(\s*' . $visibility . '\s+function\s+' . $name . ')/s', $content, $docMatch);
            $docblock = $docMatch[1] ?? '';
            
            $methods[] = [
                'name' => $name,
                'visibility' => $visibility,
                'parameters' => $this->parseParameters($params),
                'description' => $this->parseDescription($docblock),
                'return' => $this->parseReturn($docblock),
                'is_api' => str_contains($docblock, '@api'),
                'is_internal' => str_contains($docblock, '@internal'),
            ];
        }
        
        return $methods;
    }
    
    /**
     * Parse method parameters.
     */
    private function parseParameters(string $params): array
    {
        $parameters = [];
        $parts = explode(',', $params);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            preg_match('/(\??\w+)\s+\$(\w+)/', $part, $matches);
            if ($matches) {
                $parameters[] = [
                    'type' => $matches[1],
                    'name' => $matches[2],
                ];
            }
        }
        
        return $parameters;
    }
    
    /**
     * Parse return type from docblock.
     */
    private function parseReturn(string $docblock): ?string
    {
        preg_match('/@return\s+([^\s]+)/', $docblock, $match);
        return $match[1] ?? null;
    }
    
    /**
     * Parse properties from class content.
     */
    private function parseProperties(string $content): array
    {
        $properties = [];
        preg_match_all(
            '/(public|protected|private)\s+(?:readonly\s+)?(?:static\s+)?(?:const\s+)?(?:\??\w+\s+)?\$(\w+)/',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $visibility = $match[1];
            $name = $match[2];
            
            // Skip if it's a constant
            if (str_contains($match[0], 'const')) {
                continue;
            }
            
            $properties[] = [
                'name' => $name,
                'visibility' => $visibility,
            ];
        }
        
        return $properties;
    }
    
    /**
     * Generate API index page.
     */
    private function generateApiIndex(array $classes, string $outputDir): void
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>OOPress API Documentation</title>
            <link rel="stylesheet" href="/assets/css/docs.css">
        </head>
        <body>
            <div class="docs-container">
                <aside class="docs-sidebar">
                    <h2>API Reference</h2>
                    <ul>';
        
        // Group by namespace
        $grouped = [];
        foreach ($classes as $class) {
            if (!$class['is_api']) continue;
            $namespace = explode('\\', $class['namespace']);
            $group = $namespace[0] ?? 'OOPress';
            $grouped[$group][] = $class;
        }
        
        foreach ($grouped as $group => $groupClasses) {
            $html .= '<li><strong>' . htmlspecialchars($group) . '</strong>';
            $html .= '<ul>';
            foreach ($groupClasses as $class) {
                $html .= sprintf(
                    '<li><a href="/docs/api/%s.html">%s</a>%s</li>',
                    htmlspecialchars($class['name']),
                    htmlspecialchars($class['name']),
                    $class['is_internal'] ? ' <span class="internal">(internal)</span>' : ''
                );
            }
            $html .= '</ul></li>';
        }
        
        $html .= '</ul>
                </aside>
                <main class="docs-content">
                    <h1>OOPress API Documentation</h1>
                    <p>Welcome to the OOPress API documentation.</p>
                    
                    <h2>What is @api?</h2>
                    <p>Classes and methods marked with <code>@api</code> are part of the public API and are guaranteed stable within a major version.</p>
                    
                    <h2>What is @internal?</h2>
                    <p>Classes and methods marked with <code>@internal</code> are for internal use only and may change at any time.</p>
                    
                    <h2>Getting Started</h2>
                    <ul>
                        <li><a href="/docs/guides/installation.html">Installation Guide</a></li>
                        <li><a href="/docs/guides/module-development.html">Module Development</a></li>
                        <li><a href="/docs/guides/theming.html">Theming Guide</a></li>
                    </ul>
                </main>
            </div>
        </body>
        </html>';
        
        file_put_contents($outputDir . '/index.html', $html);
    }
    
    /**
     * Generate class page.
     */
    private function generateClassPage(array $class, string $outputDir): void
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>' . htmlspecialchars($class['name']) . ' - OOPress API</title>
            <link rel="stylesheet" href="/assets/css/docs.css">
        </head>
        <body>
            <div class="docs-container">
                <aside class="docs-sidebar">
                    <a href="/docs/api/index.html" class="back-link">← Back to Index</a>
                </aside>
                <main class="docs-content">
                    <div class="class-header">
                        <h1>' . htmlspecialchars($class['name']) . '</h1>
                        <div class="class-meta">
                            <span class="class-type">' . htmlspecialchars($class['type']) . '</span>
                            <span class="class-namespace">' . htmlspecialchars($class['namespace']) . '</span>
                            ' . ($class['is_internal'] ? '<span class="badge internal">Internal</span>' : '') . '
                            ' . ($class['is_api'] ? '<span class="badge api">API</span>' : '') . '
                        </div>
                    </div>
                    
                    <div class="class-description">
                        <p>' . htmlspecialchars($class['description']) . '</p>
                    </div>';
        
        // Properties
        if (!empty($class['properties'])) {
            $html .= '<h2>Properties</h2>';
            $html .= '<table class="methods-table">';
            foreach ($class['properties'] as $property) {
                $html .= sprintf(
                    '<tr>
                        <td><code>%s $%s</code></td>
                     </tr>',
                    htmlspecialchars($property['visibility']),
                    htmlspecialchars($property['name'])
                );
            }
            $html .= '</table>';
        }
        
        // Methods
        if (!empty($class['methods'])) {
            $html .= '<h2>Methods</h2>';
            foreach ($class['methods'] as $method) {
                $html .= '<div class="method">';
                $html .= sprintf(
                    '<h3><code>%s function %s(%s)%s</code>%s</h3>',
                    htmlspecialchars($method['visibility']),
                    htmlspecialchars($method['name']),
                    $this->formatParameters($method['parameters']),
                    $method['return'] ? ': ' . htmlspecialchars($method['return']) : '',
                    $method['is_api'] ? ' <span class="badge api">API</span>' : ''
                );
                
                if ($method['description']) {
                    $html .= '<p>' . htmlspecialchars($method['description']) . '</p>';
                }
                
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '
                </main>
            </div>
        </body>
        </html>';
        
        file_put_contents($outputDir . '/' . $class['name'] . '.html', $html);
    }
    
    /**
     * Format parameters for display.
     */
    private function formatParameters(array $parameters): string
    {
        $items = [];
        foreach ($parameters as $param) {
            $items[] = $param['type'] . ' $' . $param['name'];
        }
        return implode(', ', $items);
    }
    
    /**
     * Generate user guides from markdown.
     */
    private function generateGuides(DocResult $result): void
    {
        $guidesDir = $this->pathResolver->getProjectRoot() . '/' . $this->config['guides_dir'];
        $outputDir = $this->pathResolver->getPublicRoot() . '/docs/guides';
        
        if (!is_dir($guidesDir)) {
            return;
        }
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $iterator = new \DirectoryIterator($guidesDir);
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'md') {
                $this->convertMarkdownToHtml($file->getPathname(), $outputDir);
                $result->addGenerated('guides/' . $file->getBasename('.md') . '.html');
            }
        }
    }
    
    /**
     * Convert markdown to HTML.
     */
    private function convertMarkdownToHtml(string $source, string $outputDir): void
    {
        $markdown = file_get_contents($source);
        
        // Simple markdown parsing (in production, use a library like Parsedown)
        $html = $this->simpleMarkdownToHtml($markdown);
        
        $filename = basename($source, '.md') . '.html';
        $title = ucfirst(str_replace('-', ' ', basename($source, '.md')));
        
        $fullHtml = '<!DOCTYPE html>
        <html>
        <head>
            <title>' . htmlspecialchars($title) . ' - OOPress Guides</title>
            <link rel="stylesheet" href="/assets/css/docs.css">
        </head>
        <body>
            <div class="docs-container">
                <aside class="docs-sidebar">
                    <h2>Guides</h2>
                    <ul>
                        <li><a href="/docs/guides/installation.html">Installation</a></li>
                        <li><a href="/docs/guides/configuration.html">Configuration</a></li>
                        <li><a href="/docs/guides/module-development.html">Module Development</a></li>
                        <li><a href="/docs/guides/theming.html">Theming</a></li>
                        <li><a href="/docs/guides/api-usage.html">API Usage</a></li>
                        <li><a href="/docs/guides/security.html">Security</a></li>
                        <li><a href="/docs/guides/gdpr.html">GDPR Compliance</a></li>
                    </ul>
                </aside>
                <main class="docs-content">
                    <h1>' . htmlspecialchars($title) . '</h1>
                    ' . $html . '
                </main>
            </div>
        </body>
        </html>';
        
        file_put_contents($outputDir . '/' . $filename, $fullHtml);
    }
    
    /**
     * Simple markdown to HTML converter.
     */
    private function simpleMarkdownToHtml(string $markdown): string
    {
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html);
        
        // Code blocks
        $html = preg_replace('/```(\w*)\n(.*?)\n```/ms', '<pre><code class="language-$1">$2</code></pre>', $html);
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*\*(.*?)\*\*\*/', '<strong><em>$1</em></strong>', $html);
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Lists
        $html = preg_replace('/^\s*-\s+(.*?)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*?<\/li>\n)+/s', '<ul>$0</ul>', $html);
        
        $html = preg_replace('/^\s*\d+\.\s+(.*?)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*?<\/li>\n)+/s', '<ol>$0</ol>', $html);
        
        // Paragraphs
        $html = preg_replace('/\n\n([^\n<][^\n]*[^\n<])\n\n/', "\n\n<p>$1</p>\n\n", $html);
        
        // Line breaks
        $html = nl2br($html);
        
        return $html;
    }
    
    /**
     * Generate documentation index.
     */
    private function generateIndex(DocResult $result): void
    {
        $outputDir = $this->pathResolver->getPublicRoot() . '/docs';
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>OOPress Documentation</title>
            <link rel="stylesheet" href="/assets/css/docs.css">
        </head>
        <body>
            <div class="docs-container">
                <aside class="docs-sidebar">
                    <h2>Documentation</h2>
                    <ul>
                        <li><a href="/docs/api/index.html">API Reference</a></li>
                        <li><a href="/docs/guides/">User Guides</a></li>
                        <li><a href="/docs/developer/">Developer Docs</a></li>
                    </ul>
                </aside>
                <main class="docs-content">
                    <h1>OOPress Documentation</h1>
                    
                    <div class="doc-grid">
                        <div class="doc-card">
                            <h2>📚 User Guides</h2>
                            <p>Learn how to install, configure, and use OOPress.</p>
                            <a href="/docs/guides/installation.html">Get Started →</a>
                        </div>
                        <div class="doc-card">
                            <h2>🔧 Developer Docs</h2>
                            <p>Build modules, themes, and extend OOPress.</p>
                            <a href="/docs/developer/module-development.html">Start Building →</a>
                        </div>
                        <div class="doc-card">
                            <h2>📖 API Reference</h2>
                            <p>Complete API documentation for OOPress core.</p>
                            <a href="/docs/api/index.html">Browse API →</a>
                        </div>
                        <div class="doc-card">
                            <h2>🔒 GDPR Compliance</h2>
                            <p>Learn about GDPR features in OOPress.</p>
                            <a href="/docs/guides/gdpr.html">Read More →</a>
                        </div>
                    </div>
                </main>
            </div>
        </body>
        </html>';
        
        file_put_contents($outputDir . '/index.html', $html);
        $result->addGenerated('index.html');
    }
}