<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Table;

use OOPress\UI\Component\ComponentInterface;

/**
 * Pagination — Pagination component.
 * 
 * @api
 */
class Pagination implements ComponentInterface
{
    private string $name;
    private int $currentPage;
    private int $totalPages;
    private int $totalItems;
    private int $itemsPerPage;
    private array $attributes = [];
    
    public function __construct(string $name, int $currentPage, int $totalPages, int $totalItems, int $itemsPerPage = 20)
    {
        $this->name = $name;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = $totalPages;
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
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
    
    public function render(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        $html .= '<div class="pagination-info">';
        $html .= sprintf(
            'Showing %d to %d of %d items',
            (($this->currentPage - 1) * $this->itemsPerPage) + 1,
            min($this->currentPage * $this->itemsPerPage, $this->totalItems),
            $this->totalItems
        );
        $html .= '</div>';
        
        $html .= '<div class="pagination-links">';
        
        // First page
        if ($this->currentPage > 1) {
            $html .= $this->renderLink(1, '« First');
        }
        
        // Previous page
        if ($this->currentPage > 1) {
            $html .= $this->renderLink($this->currentPage - 1, '‹ Previous');
        }
        
        // Page numbers
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);
        
        if ($start > 1) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i === $this->currentPage) {
                $html .= sprintf('<span class="pagination-current">%d</span>', $i);
            } else {
                $html .= $this->renderLink($i, (string) $i);
            }
        }
        
        if ($end < $this->totalPages) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
        
        // Next page
        if ($this->currentPage < $this->totalPages) {
            $html .= $this->renderLink($this->currentPage + 1, 'Next ›');
        }
        
        // Last page
        if ($this->currentPage < $this->totalPages) {
            $html .= $this->renderLink($this->totalPages, 'Last »');
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderLink(int $page, string $label): string
    {
        $params = $_GET;
        $params['page'] = $page;
        
        $url = '?' . http_build_query($params);
        
        return sprintf('<a href="%s" class="pagination-link">%s</a>', htmlspecialchars($url), htmlspecialchars($label));
    }
}