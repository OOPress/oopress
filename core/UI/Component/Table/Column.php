<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Table;

/**
 * Column — Table column definition.
 * 
 * @api
 */
class Column
{
    private string $name;
    private string $label;
    private string $type = 'text';
    private bool $sortable = true;
    
    /**
     * @var callable|null
     */
    private $formatter = null;
    
    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getLabel(): string
    {
        return $this->label;
    }
    
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    
    public function getType(): string
    {
        return $this->type;
    }
    
    public function setSortable(bool $sortable): self
    {
        $this->sortable = $sortable;
        return $this;
    }
    
    public function isSortable(): bool
    {
        return $this->sortable;
    }
    
    /**
     * Set a formatter callback for the column.
     * 
     * @param callable $formatter Function that takes a value and returns a string
     * @return self
     */
    public function setFormatter(callable $formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }
    
    /**
     * Format a value using the formatter if set.
     * 
     * @param mixed $value The value to format
     * @return string The formatted value
     */
    public function format($value): string
    {
        if ($this->formatter !== null) {
            return (string) call_user_func($this->formatter, $value);
        }
        
        return (string) $value;
    }
}