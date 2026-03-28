<?php

declare(strict_types=1);

namespace OOPress\Security\Csrf;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * CsrfTokenManager — Manages CSRF tokens.
 * 
 * @api
 */
class CsrfTokenManager
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAMESPACE = '_csrf';
    
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}
    
    /**
     * Generate a CSRF token.
     */
    public function generateToken(string $tokenId): string
    {
        $session = $this->getSession();
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        
        $tokens = $session->get(self::TOKEN_NAMESPACE, []);
        $tokens[$tokenId] = $token;
        $session->set(self::TOKEN_NAMESPACE, $tokens);
        
        return $token;
    }
    
    /**
     * Validate a CSRF token.
     */
    public function validateToken(string $tokenId, string $token): bool
    {
        $session = $this->getSession();
        $tokens = $session->get(self::TOKEN_NAMESPACE, []);
        
        if (!isset($tokens[$tokenId])) {
            return false;
        }
        
        $valid = hash_equals($tokens[$tokenId], $token);
        
        // Remove used token
        unset($tokens[$tokenId]);
        $session->set(self::TOKEN_NAMESPACE, $tokens);
        
        return $valid;
    }
    
    /**
     * Get CSRF token HTML field.
     */
    public function getTokenField(string $tokenId): string
    {
        $token = $this->generateToken($tokenId);
        
        return sprintf(
            '<input type="hidden" name="_csrf_token" value="%s" data-csrf-id="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($tokenId, ENT_QUOTES, 'UTF-8')
        );
    }
    
    /**
     * Get CSRF token from request.
     */
    public function getTokenFromRequest(string $tokenId): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return null;
        }
        
        return $request->request->get('_csrf_token') ?: $request->headers->get('X-CSRF-Token');
    }
    
    /**
     * Refresh token (for forms that need to be submitted multiple times).
     */
    public function refreshToken(string $tokenId): string
    {
        return $this->generateToken($tokenId);
    }
    
    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request || !$request->hasSession()) {
            throw new \RuntimeException('No session available for CSRF token generation');
        }
        
        return $request->getSession();
    }
}