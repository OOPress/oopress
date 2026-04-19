<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\CookieConsent;
use OOPress\Http\Request;
use OOPress\Http\Response;

class CookieController
{
    private CookieConsent $cookieConsent;
    
    public function __construct()
    {
        $this->cookieConsent = new CookieConsent();
    }
    
    public function setConsent(Request $request): Response
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($data && is_array($data)) {
            $this->cookieConsent->setConsent($data);
        }
        
        return Response::json(['success' => true]);
    }
    
    public function revokeConsent(Request $request): Response
    {
        $this->cookieConsent->revokeConsent();
        return Response::redirect('/');
    }
}