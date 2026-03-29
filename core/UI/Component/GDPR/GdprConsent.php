<?php

declare(strict_types=1);

namespace OOPress\UI\Component\GDPR;

use OOPress\UI\Component\ComponentInterface;

/**
 * GdprConsent — GDPR consent management component.
 * 
 * GDPR compliant: Shows consent notice and manages preferences.
 * 
 * @api
 */
class GdprConsent implements ComponentInterface
{
    private string $name;
    private array $purposes = [];
    private array $cookies = [];
    private bool $showDetails = true;
    private string $privacyPolicyUrl = '';
    private array $attributes = [];
    
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->initializeDefaultPurposes();
    }
    
    private function initializeDefaultPurposes(): void
    {
        $this->purposes = [
            'necessary' => [
                'label' => 'Necessary',
                'description' => 'These cookies are required for the website to function properly.',
                'required' => true,
            ],
            'preferences' => [
                'label' => 'Preferences',
                'description' => 'These cookies remember your preferences and settings.',
                'required' => false,
            ],
            'analytics' => [
                'label' => 'Analytics',
                'description' => 'These cookies help us understand how visitors interact with the website.',
                'required' => false,
            ],
            'marketing' => [
                'label' => 'Marketing',
                'description' => 'These cookies are used to deliver relevant advertisements.',
                'required' => false,
            ],
        ];
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }
    
    /**
     * Add a purpose.
     */
    public function addPurpose(string $id, string $label, string $description, bool $required = false): self
    {
        $this->purposes[$id] = [
            'label' => $label,
            'description' => $description,
            'required' => $required,
        ];
        return $this;
    }
    
    /**
     * Add a cookie.
     */
    public function addCookie(string $name, string $purpose, string $description): self
    {
        $this->cookies[] = [
            'name' => $name,
            'purpose' => $purpose,
            'description' => $description,
        ];
        return $this;
    }
    
    /**
     * Set privacy policy URL.
     */
    public function setPrivacyPolicyUrl(string $url): self
    {
        $this->privacyPolicyUrl = $url;
        return $this;
    }
    
    /**
     * Show/hide details section.
     */
    public function setShowDetails(bool $show): self
    {
        $this->showDetails = $show;
        return $this;
    }
    
    /**
     * Render the consent banner.
     */
    public function render(): string
    {
        // Check if consent is already given
        $consentGiven = isset($_COOKIE['gdpr_consent']);
        
        if ($consentGiven) {
            return $this->renderSettingsLink();
        }
        
        return $this->renderBanner();
    }
    
    private function renderBanner(): string
    {
        $html = sprintf(
            '<div id="%s-banner" class="gdpr-consent-banner" %s>',
            htmlspecialchars($this->name),
            $this->renderAttributes()
        );
        
        $html .= '<div class="gdpr-consent-content">';
        $html .= '<p>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>';
        
        if ($this->privacyPolicyUrl) {
            $html .= sprintf(
                '<a href="%s" class="gdpr-privacy-link" target="_blank">Privacy Policy</a>',
                htmlspecialchars($this->privacyPolicyUrl)
            );
        }
        
        $html .= '</div>';
        
        $html .= '<div class="gdpr-consent-buttons">';
        
        if ($this->showDetails) {
            $html .= '<button class="gdpr-settings-button" onclick="showGdprSettings()">Cookie Settings</button>';
        }
        
        $html .= '<button class="gdpr-accept-button" onclick="acceptGdpr()">Accept All</button>';
        $html .= '<button class="gdpr-decline-button" onclick="declineGdpr()">Decline</button>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= $this->renderSettingsModal();
        $html .= $this->renderJavaScript();
        
        return $html;
    }
    
    private function renderSettingsLink(): string
    {
        return sprintf(
            '<div class="gdpr-settings-link">
                <button onclick="showGdprSettings()">Cookie Settings</button>
            </div>',
            htmlspecialchars($this->name)
        );
    }
    
    private function renderSettingsModal(): string
    {
        $html = sprintf(
            '<div id="%s-modal" class="gdpr-modal" style="display:none">',
            htmlspecialchars($this->name)
        );
        
        $html .= '<div class="gdpr-modal-overlay" onclick="hideGdprSettings()"></div>';
        $html .= '<div class="gdpr-modal-container">';
        
        $html .= '<div class="gdpr-modal-header">';
        $html .= '<h3>Cookie Preferences</h3>';
        $html .= '<button class="gdpr-modal-close" onclick="hideGdprSettings()">&times;</button>';
        $html .= '</div>';
        
        $html .= '<div class="gdpr-modal-body">';
        
        foreach ($this->purposes as $id => $purpose) {
            $html .= sprintf(
                '<div class="gdpr-purpose">
                    <label>
                        <input type="checkbox" data-purpose="%s" %s>
                        <strong>%s</strong>
                    </label>
                    <p>%s</p>
                </div>',
                htmlspecialchars($id),
                $purpose['required'] ? 'checked disabled' : '',
                htmlspecialchars($purpose['label']),
                htmlspecialchars($purpose['description'])
            );
        }
        
        if (!empty($this->cookies) && $this->showDetails) {
            $html .= '<div class="gdpr-cookie-list">';
            $html .= '<h4>Cookie Details</h4>';
            $html .= '<table>';
            $html .= '<thead><tr><th>Name</th><th>Purpose</th><th>Description</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($this->cookies as $cookie) {
                $html .= sprintf(
                    '<tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>',
                    htmlspecialchars($cookie['name']),
                    htmlspecialchars($cookie['purpose']),
                    htmlspecialchars($cookie['description'])
                );
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        $html .= '<div class="gdpr-modal-footer">';
        $html .= '<button class="gdpr-save-button" onclick="saveGdprPreferences()">Save Preferences</button>';
        $html .= '<button class="gdpr-accept-all-button" onclick="acceptAllGdpr()">Accept All</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderJavaScript(): string
    {
        $purposes = json_encode(array_keys($this->purposes));
        
        return <<<JS
<script>
function getCookie(name) {
    const value = `; \${document.cookie}`;
    const parts = value.split(`; \${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function setCookie(name, value, days) {
    let expires = '';
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
}

function getConsent() {
    const consent = getCookie('gdpr_consent');
    return consent ? JSON.parse(decodeURIComponent(consent)) : null;
}

function saveConsent(consent) {
    setCookie('gdpr_consent', encodeURIComponent(JSON.stringify(consent)), 365);
    
    // Dispatch event for modules
    window.dispatchEvent(new CustomEvent('gdpr:consent-updated', { detail: consent }));
    
    // Reload to apply changes
    location.reload();
}

function acceptAllGdpr() {
    const consent = {$purposes}.reduce((acc, purpose) => {
        acc[purpose] = true;
        return acc;
    }, {});
    saveConsent(consent);
}

function acceptGdpr() {
    acceptAllGdpr();
}

function declineGdpr() {
    const consent = {$purposes}.reduce((acc, purpose) => {
        acc[purpose] = purpose === 'necessary';
        return acc;
    }, {});
    saveConsent(consent);
}

function showGdprSettings() {
    const modal = document.getElementById('{$this->name}-modal');
    if (modal) modal.style.display = 'flex';
    
    // Load current preferences
    const consent = getConsent();
    if (consent) {
        document.querySelectorAll('[data-purpose]').forEach(checkbox => {
            const purpose = checkbox.dataset.purpose;
            if (!checkbox.disabled && consent[purpose] !== undefined) {
                checkbox.checked = consent[purpose];
            }
        });
    }
}

function hideGdprSettings() {
    const modal = document.getElementById('{$this->name}-modal');
    if (modal) modal.style.display = 'none';
}

function saveGdprPreferences() {
    const consent = {};
    document.querySelectorAll('[data-purpose]').forEach(checkbox => {
        const purpose = checkbox.dataset.purpose;
        consent[purpose] = checkbox.checked;
    });
    saveConsent(consent);
}

// Show banner if no consent
if (!getCookie('gdpr_consent')) {
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('{$this->name}-banner');
        if (banner) banner.style.display = 'flex';
    });
}
</script>
JS;
    }
    
    private function renderAttributes(): string
    {
        $attrs = [];
        foreach ($this->attributes as $key => $value) {
            $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
        }
        return implode(' ', $attrs);
    }
}