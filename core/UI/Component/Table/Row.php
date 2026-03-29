<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Table;

/**
 * Row — Table row data.
 * 
 * @api
 */
class Row
{
    private array $cells = [];
    private array $data = [];
    
    public function setCell(string $column, $value): self
    {
        $this->cells[$column] = $value;
        return $this;
    }
    
    public function getCell(string $column)
    {
        return $this->cells[$column] ?? null;
    }
    
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
}