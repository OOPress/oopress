<?php

declare(strict_types=1);

namespace OOPress\UI\Component\DragDrop;

use OOPress\UI\Component\ComponentInterface;

/**
 * DragDropItem — Item in a drag-and-drop list.
 * 
 * @api
 */
class DragDropItem implements ComponentInterface
{
    private string $key;
    private string $content;
    private int $order = 0;
    private array $attributes = [];
    
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    
    public function getName(): string
    {
        return 'dragdrop_item_' . $this->key;
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
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }
    
    public function render(): string
    {
        return sprintf(
            '<div class="dragdrop-item" data-key="%s" data-order="%d" %s>
                <div class="dragdrop-handle">⋮⋮</div>
                <div class="dragdrop-content">%s</div>
            </div>',
            htmlspecialchars($this->key, ENT_QUOTES, 'UTF-8'),
            $this->order,
            $this->renderAttributes(),
            $this->content
        );
    }
    
    private function renderAttributes(): string
    {
        $attrs = [];
        foreach ($this->attributes as $key => $value) {
            $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }
        return implode(' ', $attrs);
    }
}