<?php

declare(strict_types=1);

namespace OOPress\Http\Middleware;

use OOPress\Http\Request;
use OOPress\Http\Response;
use OOPress\Http\MiddlewareInterface;
use OOPress\Core\I18n\Translator;

class LanguageMiddleware implements MiddlewareInterface
{
    private Translator $translator;
    
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }
    
    public function process(Request $request, callable $next): Response
    {
        // Check URL parameter
        $lang = $request->input('lang');
        
        // Check session
        if (!$lang && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang'])) {
            $lang = $_SESSION['lang'];
        }
        
        // Check browser Accept-Language header
        if (!$lang) {
            $browserLang = $this->getBrowserLanguage($request);
            if ($browserLang) {
                $lang = $browserLang;
            }
        }
        
        // Default to English
        if (!$lang || !in_array($lang, $this->translator->getAvailableLocales())) {
            $lang = 'en';
        }
        
        // Set locale
        $this->translator->setLocale($lang);
        
        // Store in session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['lang'] = $lang;
        
        return $next($request);
    }
    
    private function getBrowserLanguage(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        if (!$acceptLanguage) {
            return null;
        }
        
        $available = $this->translator->getAvailableLocales();
        $preferred = explode(',', $acceptLanguage);
        
        foreach ($preferred as $lang) {
            $lang = substr($lang, 0, 2);
            if (in_array($lang, $available)) {
                return $lang;
            }
        }
        
        return null;
    }
}