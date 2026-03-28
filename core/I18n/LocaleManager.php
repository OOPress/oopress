<?php

declare(strict_types=1);

namespace OOPress\I18n;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * LocaleManager — Handles language/locale detection and negotiation.
 * 
 * @api
 */
class LocaleManager
{
    private ?string $currentLocale = null;
    
    /**
     * @var array<string> Available locales
     */
    private array $availableLocales;
    
    /**
     * @var array<string, string> Locale aliases (e.g., "en-US" -> "en")
     */
    private array $localeAliases;
    
    public function __construct(
        private readonly SessionInterface $session,
        array $availableLocales = ['en'],
        array $localeAliases = [],
    ) {
        $this->availableLocales = $availableLocales;
        $this->localeAliases = $localeAliases;
    }
    
    /**
     * Detect the best locale for the current request.
     * 
     * This implements Backdrop-style locale negotiation with configurable options:
     * 1. Session/cookie stored preference
     * 2. URL parameter (?lang=fr)
     * 3. Browser accept-language header
     * 4. Site default
     */
    public function detectLocale(Request $request, string $defaultLocale = 'en'): string
    {
        // Check session first
        if ($this->session->has('locale')) {
            $locale = $this->session->get('locale');
            if ($this->isLocaleAvailable($locale)) {
                $this->currentLocale = $locale;
                return $locale;
            }
        }
        
        // Check URL parameter
        $urlLocale = $request->query->get('lang');
        if ($urlLocale && $this->isLocaleAvailable($urlLocale)) {
            $this->currentLocale = $urlLocale;
            return $urlLocale;
        }
        
        // Check browser language
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isLocaleAvailable($browserLocale)) {
            $this->currentLocale = $browserLocale;
            return $browserLocale;
        }
        
        // Fall back to default
        $this->currentLocale = $defaultLocale;
        return $defaultLocale;
    }
    
    /**
     * Set the current locale.
     */
    public function setLocale(string $locale, bool $persistInSession = true): void
    {
        if (!$this->isLocaleAvailable($locale)) {
            throw new \InvalidArgumentException(sprintf('Locale not available: %s', $locale));
        }
        
        $this->currentLocale = $locale;
        
        if ($persistInSession) {
            $this->session->set('locale', $locale);
        }
    }
    
    /**
     * Get the current locale.
     */
    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }
    
    /**
     * Get all available locales.
     * 
     * @return array<string>
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }
    
    /**
     * Add an available locale.
     */
    public function addAvailableLocale(string $locale): void
    {
        if (!in_array($locale, $this->availableLocales, true)) {
            $this->availableLocales[] = $locale;
        }
    }
    
    /**
     * Remove an available locale.
     */
    public function removeAvailableLocale(string $locale): void
    {
        $key = array_search($locale, $this->availableLocales, true);
        if ($key !== false) {
            unset($this->availableLocales[$key]);
            $this->availableLocales = array_values($this->availableLocales);
        }
    }
    
    /**
     * Check if a locale is available.
     */
    public function isLocaleAvailable(string $locale): bool
    {
        // Check exact match
        if (in_array($locale, $this->availableLocales, true)) {
            return true;
        }
        
        // Check alias
        $resolved = $this->localeAliases[$locale] ?? null;
        if ($resolved && in_array($resolved, $this->availableLocales, true)) {
            return true;
        }
        
        // Check language part (e.g., "en-US" -> "en")
        $languagePart = explode('-', $locale)[0];
        if (in_array($languagePart, $this->availableLocales, true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the best available locale from a given locale string.
     */
    public function getBestAvailableLocale(string $locale): ?string
    {
        // Exact match
        if (in_array($locale, $this->availableLocales, true)) {
            return $locale;
        }
        
        // Alias match
        $resolved = $this->localeAliases[$locale] ?? null;
        if ($resolved && in_array($resolved, $this->availableLocales, true)) {
            return $resolved;
        }
        
        // Language part match
        $languagePart = explode('-', $locale)[0];
        if (in_array($languagePart, $this->availableLocales, true)) {
            return $languagePart;
        }
        
        return null;
    }
    
    /**
     * Get the browser's preferred locale from the request.
     */
    private function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->headers->get('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parse accept-language header
        $locales = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $localeInfo = explode(';', $part);
            $locale = trim($localeInfo[0]);
            $quality = 1.0;
            
            if (isset($localeInfo[1]) && str_starts_with($localeInfo[1], 'q=')) {
                $quality = (float) substr($localeInfo[1], 2);
            }
            
            $locales[] = ['locale' => $locale, 'quality' => $quality];
        }
        
        // Sort by quality
        usort($locales, fn($a, $b) => $b['quality'] <=> $a['quality']);
        
        // Find first available locale
        foreach ($locales as $localeInfo) {
            $best = $this->getBestAvailableLocale($localeInfo['locale']);
            if ($best) {
                return $best;
            }
        }
        
        return null;
    }
    
    /**
     * Get the locale switcher links for all available locales.
     * 
     * @param Request $request The current request
     * @param string $currentUrl The current URL (or null to use request URI)
     * @return array<string, array{url: string, label: string, active: bool}>
     */
    public function getLocaleSwitcher(Request $request, ?string $currentUrl = null): array
    {
        $currentUrl = $currentUrl ?? $request->getRequestUri();
        $switcher = [];
        
        foreach ($this->availableLocales as $locale) {
            // Parse current URL
            $url = parse_url($currentUrl);
            $query = [];
            
            if (isset($url['query'])) {
                parse_str($url['query'], $query);
            }
            
            // Set or replace lang parameter
            $query['lang'] = $locale;
            
            // Rebuild URL
            $newUrl = $url['path'] ?? '';
            if ($query) {
                $newUrl .= '?' . http_build_query($query);
            }
            if (isset($url['fragment'])) {
                $newUrl .= '#' . $url['fragment'];
            }
            
            $switcher[$locale] = [
                'url' => $newUrl,
                'label' => $this->getLocaleLabel($locale),
                'active' => $locale === $this->currentLocale,
            ];
        }
        
        return $switcher;
    }
    
    /**
     * Get a human-readable label for a locale.
     */
    private function getLocaleLabel(string $locale): string
    {
        $labels = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ar' => 'العربية',
        ];
        
        return $labels[$locale] ?? strtoupper($locale);
    }
}
