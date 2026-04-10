<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Http\Request;
use OOPress\Http\Response;

class PostController
{
    public function home(Request $request): Response
    {
        $lang = $request->attribute('lang');
        
        // Set language if provided in URL
        if ($lang && function_exists('set_locale')) {
            set_locale($lang);
        }
        
        $content = '<h1>' . __('Welcome to OOPress') . '</h1>';
        $content .= '<p>' . __('Current language: {{lang}}', 'default', ['lang' => get_locale()]) . '</p>';
        $content .= '<ul>';
        $content .= '<li><a href="/about">' . __('About') . '</a></li>';
        $content .= '<li><a href="/en/about">English About</a></li>';
        $content .= '<li><a href="/es/about">Spanish About</a></li>';
        $content .= '<li><a href="/fr/about">French About</a></li>';
        $content .= '</ul>';
        
        return new Response($content);
    }
    
    public function show(Request $request): Response
    {
        $slug = $request->attribute('slug');
        $lang = $request->attribute('lang');
        
        if ($lang && function_exists('set_locale')) {
            set_locale($lang);
        }
        
        // Simulate post not found
        if ($slug === 'not-found') {
            return new Response('<h1>' . __('Post not found') . '</h1>', 404);
        }
        
        $content = '<h1>Post: ' . htmlspecialchars($slug) . '</h1>';
        $content .= '<p>' . __('by {{author}}', 'default', ['author' => 'OOPress']) . '</p>';
        $content .= '<p><a href="/">' . __('Home') . '</a></p>';
        
        return new Response($content);
    }
    
    public function about(Request $request): Response
    {
        $lang = $request->attribute('lang');
        
        if ($lang && function_exists('set_locale')) {
            set_locale($lang);
        }
        
        $content = '<h1>' . __('About OOPress') . '</h1>';
        $content .= '<p>A lean, modern PHP CMS with i18n support.</p>';
        $content .= '<p><a href="/">' . __('Home') . '</a></p>';
        
        return new Response($content);
    }
}