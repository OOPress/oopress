<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Table;

use OOPress\UI\Component\ComponentInterface;

/**
 * Table — Data table component with sorting and pagination.
 * 
 * @api
 */
class Table implements ComponentInterface
{
    private string $name;
    private array $columns = [];
    private array $rows = [];
    private array $actions = [];
    private bool $sortable = true;
    private string $sortColumn = '';
    private string $sortDirection = 'asc';
    private array $attributes = [];
    private ?Pagination $pagination = null;
    
    public function __construct(string $name)
    {
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
     * Add a column.
     */
    public function addColumn(Column $column): self
    {
        $this->columns[$column->getName()] = $column;
        return $this;
    }
    
    /**
     * Add a row.
     */
    public function addRow(Row $row): self
    {
        $this->rows[] = $row;
        return $this;
    }
    
    /**
     * Set rows from data array.
     */
    public function setData(array $data, array $fieldMap): self
    {
        foreach ($data as $item) {
            $row = new Row();
            foreach ($fieldMap as $columnName => $fieldName) {
                $value = $this->getNestedValue($item, $fieldName);
                $row->setCell($columnName, $value);
            }
            $this->addRow($row);
        }
        
        return $this;
    }
    
    /**
     * Add a row action.
     */
    public function addAction(string $label, string $url, string $method = 'GET', array $attributes = []): self
    {
        $this->actions[] = [
            'label' => $label,
            'url' => $url,
            'method' => $method,
            'attributes' => $attributes,
        ];
        return $this;
    }
    
    /**
     * Set pagination.
     */
    public function setPagination(Pagination $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }
    
    /**
     * Set sorting.
     */
    public function setSorting(string $column, string $direction = 'asc'): self
    {
        $this->sortColumn = $column;
        $this->sortDirection = $direction;
        return $this;
    }
    
    /**
     * Enable/disable sorting.
     */
    public function setSortable(bool $sortable): self
    {
        $this->sortable = $sortable;
        return $this;
    }
    
    /**
     * Render the table.
     */
    public function render(): string
    {
        $html = sprintf('<table class="data-table" %s>', $this->renderAttributes());
        
        // Header
        $html .= '<thead><tr>';
        foreach ($this->columns as $column) {
            $html .= $this->renderHeaderCell($column);
        }
        if (!empty($this->actions)) {
            $html .= '<th class="actions-column">Actions</th>';
        }
        $html .= '</tr></thead>';
        
        // Body
        $html .= '<tbody>';
        foreach ($this->rows as $row) {
            $html .= '<tr>';
            foreach ($this->columns as $column) {
                $value = $row->getCell($column->getName());
                $html .= sprintf('<td%s>%s</td>', $this->renderCellAttributes($column), $value);
            }
            if (!empty($this->actions)) {
                $html .= '<td class="actions">' . $this->renderActions($row) . '</td>';
            }
            $html .= '</tr>';
        }
        
        if (empty($this->rows)) {
            $colspan = count($this->columns) + (empty($this->actions) ? 0 : 1);
            $html .= sprintf('<tr><td colspan="%d" class="empty-state">No data available</td></tr>', $colspan);
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        // Pagination
        if ($this->pagination) {
            $html .= $this->pagination->render();
        }
        
        return $html;
    }
    
    private function renderHeaderCell(Column $column): string
    {
        $label = $column->getLabel();
        
        if ($this->sortable && $column->isSortable()) {
            $direction = '';
            $icon = '';
            
            if ($this->sortColumn === $column->getName()) {
                $direction = $this->sortDirection;
                $icon = $direction === 'asc' ? '↑' : '↓';
            }
            
            $url = $this->getSortUrl($column->getName(), $direction === 'asc' ? 'desc' : 'asc');
            
            return sprintf(
                '<th><a href="%s" class="sortable %s">%s %s</a></th>',
                htmlspecialchars($url),
                $direction,
                htmlspecialchars($label),
                $icon
            );
        }
        
        return sprintf('<th>%s</th>', htmlspecialchars($label));
    }
    
    private function renderActions(Row $row): string
    {
        $html = '<div class="action-buttons">';
        
        foreach ($this->actions as $action) {
            $url = $this->replacePlaceholders($action['url'], $row->getData());
            
            $html .= sprintf(
                '<a href="%s" class="button button-small" %s>%s</a>',
                htmlspecialchars($url),
                $this->renderAttributes($action['attributes']),
                htmlspecialchars($action['label'])
            );
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function renderCellAttributes(Column $column): string
    {
        $classes = ['cell-' . $column->getName()];
        
        if ($column->getType() === 'number') {
            $classes[] = 'text-right';
        }
        
        return ' class="' . implode(' ', $classes) . '"';
    }
    
    private function renderAttributes(?array $attrs = null): string
    {
        $attrs = $attrs ?? $this->attributes;
        $result = [];
        
        foreach ($attrs as $key => $value) {
            $result[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
        }
        
        return implode(' ', $result);
    }
    
    private function getSortUrl(string $column, string $direction): string
    {
        $params = $_GET;
        $params['sort'] = $column;
        $params['direction'] = $direction;
        
        return '?' . http_build_query($params);
    }
    
    private function replacePlaceholders(string $url, array $data): string
    {
        foreach ($data as $key => $value) {
            $url = str_replace('{' . $key . '}', urlencode((string) $value), $url);
        }
        
        return $url;
    }
    
    private function getNestedValue(array $data, string $path)
    {
        $parts = explode('.', $path);
        $value = $data;
        
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return null;
            }
            $value = $value[$part];
        }
        
        return $value;
    }
}