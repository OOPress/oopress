<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Modal;

use OOPress\UI\Component\ComponentInterface;

/**
 * Modal — Modal dialog component.
 * 
 * @api
 */
class Modal implements ComponentInterface
{
    private string $id;
    private string $title;
    private string $content;
    private string $size = 'medium';
    private bool $closable = true;
    private array $buttons = [];
    private array $attributes = [];
    
    public function __construct(string $id, string $title, string $content)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
    }
    
    public function getName(): string
    {
        return $this->id;
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
    
    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }
    
    public function setClosable(bool $closable): self
    {
        $this->closable = $closable;
        return $this;
    }
    
    public function addButton(string $label, string $action, string $type = 'button'): self
    {
        $this->buttons[] = [
            'label' => $label,
            'action' => $action,
            'type' => $type,
        ];
        return $this;
    }
    
    public function render(): string
    {
        $sizeClass = match($this->size) {
            'small' => 'modal-small',
            'large' => 'modal-large',
            'full' => 'modal-full',
            default => 'modal-medium',
        };
        
        $html = sprintf(
            '<div id="%s" class="modal %s" style="display:none" %s>',
            htmlspecialchars($this->id),
            $sizeClass,
            $this->renderAttributes()
        );
        
        $html .= '<div class="modal-overlay"></div>';
        $html .= '<div class="modal-container">';
        
        // Header
        $html .= '<div class="modal-header">';
        $html .= sprintf('<h3>%s</h3>', htmlspecialchars($this->title));
        if ($this->closable) {
            $html .= '<button class="modal-close">&times;</button>';
        }
        $html .= '</div>';
        
        // Body
        $html .= sprintf('<div class="modal-body">%s</div>', $this->content);
        
        // Footer
        if (!empty($this->buttons)) {
            $html .= '<div class="modal-footer">';
            foreach ($this->buttons as $button) {
                $html .= sprintf(
                    '<button type="button" data-action="%s" class="button button-%s">%s</button>',
                    htmlspecialchars($button['action']),
                    htmlspecialchars($button['type']),
                    htmlspecialchars($button['label'])
                );
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
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