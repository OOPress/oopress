<?php

declare(strict_types=1);

namespace OOPress\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticator — Handles user authentication.
 * 
 * @api
 */
class Authenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserProvider $userProvider,
    ) {}
    
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }
    
    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');
        
        return new Passport(
            new UserBadge($username, fn($username) => $this->userProvider->loadUserByUsername($username)),
            new PasswordCredentials($password)
        );
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirect to dashboard or original destination
        $targetUrl = $request->getSession()->get('_security.main.target_path', '/admin');
        $request->getSession()->remove('_security.main.target_path');
        
        return new RedirectResponse($targetUrl);
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('login_error', $exception->getMessage());
        
        return new RedirectResponse('/login');
    }
    
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $request->getSession()->set('_security.main.target_path', $request->getPathInfo());
        
        return new RedirectResponse('/login');
    }
}