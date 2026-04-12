<?php

declare(strict_types=1);

namespace OOPress\Core;

use OOPress\Models\Post;
use OOPress\Models\Setting;
use OOPress\Models\Term;

class SEO
{
    private array $metaTags = [];
    private array $openGraph = [];
    private array $twitterCards = [];
    private array $jsonLd = [];
    private string $canonical = '';
    private string $title = '';
    private string $description = '';
    
    public function __construct()
    {
        $this->setDefaults();
    }
    
    private function setDefaults(): void
    {
        $this->title = Setting::get('site_title', 'OOPress');
        $this->description = Setting::get('meta_description', '');
        $this->canonical = $this->getCurrentUrl();
        
        // Default meta tags
        $this->metaTags['charset'] = 'utf-8';
        $this->metaTags['viewport'] = 'width=device-width, initial-scale=1.0';
        
        if ($this->description) {
            $this->metaTags['description'] = $this->description;
        }
        
        // Default Open Graph
        $this->openGraph['og:type'] = 'website';
        $this->openGraph['og:title'] = $this->title;
        $this->openGraph['og:description'] = $this->description;
        $this->openGraph['og:url'] = $this->canonical;
        $this->openGraph['og:site_name'] = Setting::get('site_title', 'OOPress');
        
        // Default Twitter Cards
        $this->twitterCards['twitter:card'] = 'summary';
        $this->twitterCards['twitter:title'] = $this->title;
        $this->twitterCards['twitter:description'] = $this->description;
    }
    
    public function setPost(Post $post): void
    {
        // Title: Use meta_title if set, otherwise post title
        $title = $post->meta_title ?: $post->title;
        $this->title = $title . ' | ' . Setting::get('site_title', 'OOPress');
        
        // Description: Use meta_description if set, otherwise excerpt or content summary
        if ($post->meta_description) {
            $this->description = $post->meta_description;
        } elseif ($post->excerpt) {
            $this->description = $post->excerpt;
        } else {
            $this->description = substr(strip_tags($post->content), 0, 160);
        }
        
        // Keywords
        if ($post->meta_keywords) {
            $this->metaTags['keywords'] = $post->meta_keywords;
        }
        
        // Canonical URL
        $this->canonical = $post->canonical_url ?: $this->getCurrentUrl();
        
        // Open Graph
        $this->openGraph['og:type'] = 'article';
        $this->openGraph['og:title'] = $title;
        $this->openGraph['og:description'] = $this->description;
        $this->openGraph['og:url'] = $this->canonical;
        
        if ($post->og_title) {
            $this->openGraph['og:title'] = $post->og_title;
        }
        if ($post->og_description) {
            $this->openGraph['og:description'] = $post->og_description;
        }
        if ($post->og_image) {
            $this->openGraph['og:image'] = $post->og_image;
        }
        
        // Twitter Cards
        $this->twitterCards['twitter:title'] = $this->openGraph['og:title'];
        $this->twitterCards['twitter:description'] = $this->openGraph['og:description'];
        if ($post->og_image) {
            $this->twitterCards['twitter:image'] = $post->og_image;
        }
        
        // JSON-LD Schema
        $this->jsonLd = $this->generateArticleSchema($post);
        
        // Meta tags
        $this->metaTags['title'] = $this->title;
        $this->metaTags['description'] = $this->description;
    }
    
    public function setArchive(string $title, string $description = '', array $items = []): void
    {
        $this->title = $title . ' | ' . Setting::get('site_title', 'OOPress');
        $this->description = $description ?: "Browse {$title} archive";
        $this->metaTags['title'] = $this->title;
        $this->metaTags['description'] = $this->description;
        
        $this->openGraph['og:type'] = 'website';
        $this->openGraph['og:title'] = $title;
        $this->openGraph['og:description'] = $this->description;
        
        // JSON-LD for archive
        $this->jsonLd = $this->generateCollectionSchema($title, $description, $items);
    }
    
    public function setHomepage(): void
    {
        $this->title = Setting::get('site_title', 'OOPress');
        $tagline = Setting::get('site_tagline', '');
        if ($tagline) {
            $this->title .= ' - ' . $tagline;
        }
        
        $this->metaTags['title'] = $this->title;
        $this->metaTags['description'] = Setting::get('meta_description', '');
        
        $this->openGraph['og:type'] = 'website';
        $this->openGraph['og:title'] = Setting::get('site_title', 'OOPress');
        $this->openGraph['og:description'] = Setting::get('meta_description', '');
        
        // JSON-LD for website
        $this->jsonLd = $this->generateWebsiteSchema();
    }
    
    public function set404(): void
    {
        $this->title = '404 - Page Not Found | ' . Setting::get('site_title', 'OOPress');
        $this->description = 'The page you are looking for could not be found.';
        $this->metaTags['title'] = $this->title;
        $this->metaTags['description'] = $this->description;
        $this->metaTags['robots'] = 'noindex, follow';
    }
    
    private function generateArticleSchema(Post $post): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $post->schema_type ?: 'Article',
            'headline' => $post->title,
            'description' => $this->description,
            'url' => $this->canonical,
            'datePublished' => $post->published_at ?? $post->created_at,
            'dateModified' => $post->updated_at,
            'author' => [
                '@type' => 'Person',
                'name' => $post->author() ? $post->author()->display_name : 'Unknown'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => Setting::get('site_title', 'OOPress'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => Setting::get('site_logo', '')
                ]
            ]
        ];
        
        if ($post->og_image) {
            $schema['image'] = $post->og_image;
        }
        
        return $schema;
    }
    
    private function generateWebsiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => Setting::get('site_title', 'OOPress'),
            'url' => $this->getBaseUrl(),
            'description' => Setting::get('meta_description', ''),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->getBaseUrl() . '/search?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }
    
    private function generateCollectionSchema(string $title, string $description, array $items): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $title,
            'description' => $description,
            'url' => $this->canonical
        ];
        
        if (!empty($items)) {
            $schema['mainEntity'] = [
                '@type' => 'ItemList',
                'itemListElement' => array_map(function($item, $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'url' => $item['url']
                    ];
                }, $items, array_keys($items))
            ];
        }
        
        return $schema;
    }
    
    public function render(): string
    {
        $html = "\n<!-- SEO Meta Tags -->\n";
        
        // Title
        $html .= "<title>" . htmlspecialchars($this->title) . "</title>\n";
        
        // Meta tags
        foreach ($this->metaTags as $name => $content) {
            if ($name === 'title') continue;
            if (in_array($name, ['charset', 'viewport'])) {
                $html .= "<meta {$name}=\"{$content}\">\n";
            } else {
                $html .= "<meta name=\"{$name}\" content=\"" . htmlspecialchars($content) . "\">\n";
            }
        }
        
        // Canonical URL
        if ($this->canonical) {
            $html .= "<link rel=\"canonical\" href=\"" . htmlspecialchars($this->canonical) . "\">\n";
        }
        
        // Open Graph
        $html .= "\n<!-- Open Graph -->\n";
        foreach ($this->openGraph as $property => $content) {
            if ($content) {
                $html .= "<meta property=\"{$property}\" content=\"" . htmlspecialchars($content) . "\">\n";
            }
        }
        
        // Twitter Cards
        $html .= "\n<!-- Twitter Cards -->\n";
        foreach ($this->twitterCards as $name => $content) {
            if ($content) {
                $html .= "<meta name=\"{$name}\" content=\"" . htmlspecialchars($content) . "\">\n";
            }
        }
        
        // JSON-LD Schema
        if (!empty($this->jsonLd)) {
            $html .= "\n<!-- JSON-LD Schema -->\n";
            $html .= "<script type=\"application/ld+json\">\n";
            $html .= json_encode($this->jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $html .= "\n</script>\n";
        }
        
        return $html;
    }
    
    private function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . '://' . $host . $uri;
    }
    
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
}