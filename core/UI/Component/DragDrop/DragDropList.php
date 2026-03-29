<?php

declare(strict_types=1);

namespace OOPress\UI\Component\DragDrop;

use OOPress\UI\Component\ComponentInterface;

/**
 * DragDropList — Drag-and-drop sortable list component.
 * 
 * @api
 */
class DragDropList implements ComponentInterface
{
    private string $id;
    private string $name;
    
    /**
     * @var array<DragDropItem>
     */
    private array $items = [];
    
    private string $itemTemplate = '';
    private array $attributes = [];
    
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
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
     * Add an item to the list.
     */
    public function addItem(DragDropItem $item): self
    {
        $this->items[] = $item;
        return $this;
    }
    
    /**
     * Set items from array.
     * 
     * @param array $items Array of items
     * @param callable|null $renderer Optional renderer callback
     * @return self
     */
    public function setItems(array $items, ?callable $renderer = null): self
    {
        foreach ($items as $key => $data) {
            $item = new DragDropItem((string) $key);
            if ($renderer !== null) {
                $item->setContent($renderer($data));
            } else {
                $item->setContent(is_string($data) ? $data : json_encode($data));
            }
            $this->addItem($item);
        }
        
        return $this;
    }
    
    /**
     * Set item template.
     */
    public function setItemTemplate(string $template): self
    {
        $this->itemTemplate = $template;
        return $this;
    }
    
    public function render(): string
    {
        $html = sprintf(
            '<div id="%s" class="dragdrop-list" data-name="%s" %s>',
            htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8'),
            $this->renderAttributes()
        );
        
        $html .= '<div class="dragdrop-items">';
        
        foreach ($this->items as $index => $item) {
            $html .= $item->setOrder($index)->render();
        }
        
        $html .= '</div>';
        
        // Hidden input for order
        $keys = [];
        foreach ($this->items as $item) {
            $keys[] = $item->getName();
        }
        
        $html .= sprintf(
            '<input type="hidden" name="%s_order" value="%s">',
            htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(implode(',', $keys), ENT_QUOTES, 'UTF-8')
        );
        
        $html .= '</div>';
        
        // JavaScript for drag-and-drop
        $html .= $this->renderJavaScript();
        
        return $html;
    }
    
    private function renderJavaScript(): string
    {
        $id = $this->id;
        
        return <<<JS
<script>
(function() {
    const container = document.getElementById('{$id}');
    if (!container) return;
    
    const items = container.querySelector('.dragdrop-items');
    let dragSrc = null;
    
    items.addEventListener('dragstart', function(e) {
        dragSrc = e.target.closest('.dragdrop-item');
        if (dragSrc) dragSrc.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
    });
    
    items.addEventListener('dragend', function(e) {
        const item = e.target.closest('.dragdrop-item');
        if (item) item.style.opacity = '';
    });
    
    items.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });
    
    items.addEventListener('drop', function(e) {
        e.preventDefault();
        const target = e.target.closest('.dragdrop-item');
        if (!target || target === dragSrc) return;
        
        // Reorder items
        const parent = target.parentNode;
        const children = Array.from(parent.children);
        const srcIndex = children.indexOf(dragSrc);
        const targetIndex = children.indexOf(target);
        
        if (srcIndex < targetIndex) {
            parent.insertBefore(dragSrc, target.nextSibling);
        } else {
            parent.insertBefore(dragSrc, target);
        }
        
        // Update order input
        const orderInput = container.querySelector('input[type="hidden"]');
        const newOrder = Array.from(parent.children).map(item => item.dataset.key);
        orderInput.value = newOrder.join(',');
        
        if (dragSrc) dragSrc.style.opacity = '';
    });
    
    // Make items draggable
    container.querySelectorAll('.dragdrop-item').forEach(item => {
        item.setAttribute('draggable', 'true');
    });
})();
</script>
JS;
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