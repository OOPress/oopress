<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Notification;

use OOPress\UI\Component\ComponentInterface;

/**
 * Notification — Notification component.
 * 
 * @api
 */
class Notification implements ComponentInterface
{
    private string $type;
    private string $message;
    private bool $dismissible = true;
    private int $duration = 5000;
    private array $attributes = [];
    
    public function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
    
    public function getName(): string
    {
        return 'notification_' . md5($this->message);
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
    
    public function setDismissible(bool $dismissible): self
    {
        $this->dismissible = $dismissible;
        return $this;
    }
    
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }
    
    public function render(): string
    {
        $icon = match($this->type) {
            'success' => '✓',
            'error' => '✗',
            'warning' => '⚠',
            'info' => 'ℹ',
            default => '',
        };
        
        $html = sprintf(
            '<div class="notification notification-%s" data-duration="%d" %s>',
            htmlspecialchars($this->type),
            $this->duration,
            $this->renderAttributes()
        );
        
        $html .= '<div class="notification-icon">' . $icon . '</div>';
        $html .= '<div class="notification-content">' . htmlspecialchars($this->message) . '</div>';
        
        if ($this->dismissible) {
            $html .= '<button class="notification-close">&times;</button>';
        }
        
        $html .= '</div>';
        
        return $html;
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