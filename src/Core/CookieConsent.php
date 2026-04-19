<?php

declare(strict_types=1);

namespace OOPress\Core;

class CookieConsent
{
    private const CONSENT_COOKIE = 'oopress_cookie_consent';
    private const CONSENT_EXPIRY = 365; // days
    
    private array $categories = [
        'necessary' => [
            'name' => 'Necessary',
            'description' => 'Essential cookies for the website to function properly.',
            'required' => true,
            'cookies' => ['session', 'csrf_token']
        ],
        'functional' => [
            'name' => 'Functional',
            'description' => 'Enhance functionality and personalization.',
            'required' => false,
            'cookies' => ['user_preferences', 'language']
        ],
        'analytics' => [
            'name' => 'Analytics',
            'description' => 'Help us understand how visitors interact with the site.',
            'required' => false,
            'cookies' => ['_ga', '_gid', '_gat']
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Used to track visitors across websites.',
            'required' => false,
            'cookies' => ['_fbp', '_gcl_au']
        ]
    ];
    
    /**
     * Check if user has given consent
     */
    public function hasConsent(): bool
    {
        return isset($_COOKIE[self::CONSENT_COOKIE]);
    }
    
    /**
     * Get user's consent preferences
     */
    public function getConsent(): array
    {
        if (!$this->hasConsent()) {
            return [];
        }
        
        $consent = json_decode($_COOKIE[self::CONSENT_COOKIE], true);
        return is_array($consent) ? $consent : [];
    }
    
    /**
     * Check if specific category is allowed
     */
    public function isCategoryAllowed(string $category): bool
    {
        if (!isset($this->categories[$category])) {
            return false;
        }
        
        // Necessary cookies are always allowed
        if ($this->categories[$category]['required']) {
            return true;
        }
        
        $consent = $this->getConsent();
        return isset($consent[$category]) && $consent[$category] === true;
    }
    
    /**
     * Set user consent
     */
    public function setConsent(array $preferences): void
    {
        $expiry = time() + (self::CONSENT_EXPIRY * 24 * 60 * 60);
        setcookie(self::CONSENT_COOKIE, json_encode($preferences), $expiry, '/', '', false, true);
    }
    
    /**
     * Revoke consent
     */
    public function revokeConsent(): void
    {
        setcookie(self::CONSENT_COOKIE, '', time() - 3600, '/');
    }
    
    /**
     * Get all cookie categories
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
    
    /**
     * Render cookie banner HTML
     */
    public function renderBanner(): string
    {
        if ($this->hasConsent()) {
            return '';
        }
        
        $html = '
        <div id="cookie-banner" class="cookie-banner">
            <div class="cookie-banner-content">
                <div class="cookie-banner-text">
                    <h3>' . __('We value your privacy') . '</h3>
                    <p>' . __('We use cookies to enhance your browsing experience. By clicking "Accept All", you consent to our use of cookies.') . ' <a href="/privacy-policy" style="color: #4299e1;">' . __('Privacy Policy') . '</a> | <a href="/imprint" style="color: #4299e1;">' . __('Imprint') . '</a></p>
                </div>
                <div class="cookie-banner-buttons">
                    <button type="button" class="cookie-btn cookie-btn-primary" id="cookie-accept-all">
                        ' . __('Accept All') . '
                    </button>
                    <button type="button" class="cookie-btn cookie-btn-secondary" id="cookie-preferences">
                        ' . __('Preferences') . '
                    </button>
                    <button type="button" class="cookie-btn cookie-btn-secondary" id="cookie-accept-necessary">
                        ' . __('Accept Necessary Only') . '
                    </button>
                </div>
            </div>
        </div>
        
        <div id="cookie-modal" class="cookie-modal" style="display: none;">
            <div class="cookie-modal-content">
                <div class="cookie-modal-header">
                    <h3>' . __('Cookie Preferences') . '</h3>
                    <button type="button" class="cookie-modal-close" id="cookie-modal-close">&times;</button>
                </div>
                <div class="cookie-modal-body">
                    <p>' . __('Manage your cookie preferences below. Necessary cookies are required for the website to function properly.') . '</p>
                    
                    <div class="cookie-categories">';
        
        foreach ($this->categories as $key => $category) {
            $checked = $category['required'] ? 'checked disabled' : 'checked';
            $html .= '
                        <div class="cookie-category">
                            <div class="cookie-category-header">
                                <label class="cookie-switch">
                                    <input type="checkbox" class="cookie-category-checkbox" data-category="' . $key . '" ' . $checked . '>
                                    <span class="cookie-slider"></span>
                                </label>
                                <div>
                                    <h4>' . __($category['name']) . '</h4>
                                    <p>' . __($category['description']) . '</p>
                                </div>
                            </div>
                        </div>';
        }
        
        $html .= '
                    </div>
                </div>
                <div class="cookie-modal-footer">
                    <button type="button" class="cookie-btn cookie-btn-primary" id="cookie-save-preferences">
                        ' . __('Save Preferences') . '
                    </button>
                    <button type="button" class="cookie-btn cookie-btn-secondary" id="cookie-accept-all-modal">
                        ' . __('Accept All') . '
                    </button>
                </div>
            </div>
        </div>
        
        <style>
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a202c;
            color: white;
            z-index: 10000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        
        .cookie-banner-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .cookie-banner-text {
            flex: 1;
        }
        
        .cookie-banner-text h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        
        .cookie-banner-text p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .cookie-banner-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .cookie-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .cookie-btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .cookie-btn-primary:hover {
            background: #3182ce;
        }
        
        .cookie-btn-secondary {
            background: #4a5568;
            color: white;
        }
        
        .cookie-btn-secondary:hover {
            background: #2d3748;
        }
        
        .cookie-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cookie-modal-content {
            background: white;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .cookie-modal-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cookie-modal-header h3 {
            margin: 0;
        }
        
        .cookie-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #718096;
        }
        
        .cookie-modal-body {
            padding: 20px;
        }
        
        .cookie-categories {
            margin-top: 20px;
        }
        
        .cookie-category {
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .cookie-category-header {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cookie-category-header h4 {
            margin: 0 0 5px 0;
        }
        
        .cookie-category-header p {
            margin: 0;
            font-size: 12px;
            color: #718096;
        }
        
        .cookie-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            flex-shrink: 0;
        }
        
        .cookie-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .cookie-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: 0.3s;
            border-radius: 24px;
        }
        
        .cookie-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        
        input:checked + .cookie-slider {
            background-color: #4299e1;
        }
        
        input:checked + .cookie-slider:before {
            transform: translateX(26px);
        }
        
        input:disabled + .cookie-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .cookie-modal-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        </style>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const banner = document.getElementById("cookie-banner");
            const modal = document.getElementById("cookie-modal");
            
            // Accept all cookies
            document.getElementById("cookie-accept-all")?.addEventListener("click", function() {
                setConsent({
                    necessary: true,
                    functional: true,
                    analytics: true,
                    marketing: true
                });
                banner.style.display = "none";
                location.reload();
            });
            
            // Accept only necessary
            document.getElementById("cookie-accept-necessary")?.addEventListener("click", function() {
                setConsent({
                    necessary: true,
                    functional: false,
                    analytics: false,
                    marketing: false
                });
                banner.style.display = "none";
                location.reload();
            });
            
            // Open preferences modal
            document.getElementById("cookie-preferences")?.addEventListener("click", function() {
                modal.style.display = "flex";
            });
            
            // Close modal
            document.getElementById("cookie-modal-close")?.addEventListener("click", function() {
                modal.style.display = "none";
            });
            
            // Save preferences
            document.getElementById("cookie-save-preferences")?.addEventListener("click", function() {
                const preferences = {
                    necessary: true,
                    functional: false,
                    analytics: false,
                    marketing: false
                };
                
                document.querySelectorAll(".cookie-category-checkbox").forEach(function(checkbox) {
                    if (!checkbox.disabled) {
                        preferences[checkbox.dataset.category] = checkbox.checked;
                    }
                });
                
                setConsent(preferences);
                modal.style.display = "none";
                location.reload();
            });
            
            // Accept all from modal
            document.getElementById("cookie-accept-all-modal")?.addEventListener("click", function() {
                setConsent({
                    necessary: true,
                    functional: true,
                    analytics: true,
                    marketing: true
                });
                modal.style.display = "none";
                location.reload();
            });
            
            function setConsent(preferences) {
                fetch("/cookie-consent/set", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(preferences)
                });
            }
        });
        </script>';
        
        return $html;
    }
}