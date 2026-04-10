<?php

declare(strict_types=1);

namespace OOPress\Core\I18n;

class Translator
{
    private array $translations = [];
    private string $locale;
    private string $fallbackLocale = 'en';
    private array $loadedDomains = [];
    
    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
    }
    
    /**
     * Set current locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        setlocale(LC_ALL, $locale . '.utf8', $locale);
    }
    
    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
    
    /**
     * Set fallback locale
     */
    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }
    
    /**
     * Load translation file for a domain
     */
    public function load(string $domain, string $locale = null): void
    {
        $locale = $locale ?? $this->locale;
        $key = "{$domain}.{$locale}";
        
        if (isset($this->loadedDomains[$key])) {
            return;
        }
        
        $paths = [
            __DIR__ . "/../../../lang/{$locale}/{$domain}.php",
            __DIR__ . "/../../../lang/{$this->fallbackLocale}/{$domain}.php",
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $this->translations[$domain][$locale] = require $path;
                $this->loadedDomains[$key] = true;
                return;
            }
        }
        
        $this->translations[$domain][$locale] = [];
        $this->loadedDomains[$key] = true;
    }
    
    /**
     * Translate a string
     */
    public function translate(string $text, string $domain = 'default', array $params = []): string
    {
        // Load domain if not loaded
        if (!isset($this->loadedDomains["{$domain}.{$this->locale}"])) {
            $this->load($domain);
        }
        
        // Get translation
        $translated = $this->translations[$domain][$this->locale][$text] ?? 
                      $this->translations[$domain][$this->fallbackLocale][$text] ?? 
                      $text;
        
        // Replace parameters
        foreach ($params as $key => $value) {
            $translated = str_replace("{{$key}}", $value, $translated);
        }
        
        return $translated;
    }
    
    /**
     * Translate with context (singular/plural)
     */
    public function translatePlural(
        string $singular, 
        string $plural, 
        int $number, 
        string $domain = 'default',
        array $params = []
    ): string {
        $text = $number == 1 ? $singular : $plural;
        $params['count'] = $number;
        return $this->translate($text, $domain, $params);
    }
    
    /**
     * Get all available locales
     */
    public function getAvailableLocales(): array
    {
        $langPath = __DIR__ . '/../../../lang';
        if (!is_dir($langPath)) {
            return ['en'];
        }
        
        $locales = [];
        $dirs = glob($langPath . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $locales[] = basename($dir);
        }
        
        return empty($locales) ? ['en'] : $locales;
    }
}