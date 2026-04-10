<?php

declare(strict_types=1);

namespace OOPress\Core\Database;

use Medoo\Medoo;

class QueryBuilder
{
    private Medoo $db;
    private string $modelClass;
    private array $conditions = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        // Get database connection from the model
        $this->db = $modelClass::getDB();
    }
    
    public function where(array $conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = [$column => $direction];
        return $this;
    }
    
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function get(): array
    {
        $query = $this->conditions;
        
        if (!empty($this->orderBy)) {
            $query['ORDER'] = $this->orderBy;
        }
        
        if ($this->limit) {
            $query['LIMIT'] = $this->limit;
        }
        
        if ($this->offset) {
            $query['LIMIT'] = [$this->offset, $this->limit];
        }
        
        $results = $this->db->select($this->modelClass::getTable(), '*', $query);
        
        return array_map(function($data) {
            return new $this->modelClass($data);
        }, $results);
    }
    
    public function first(): ?object
    {
        $this->limit = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }
    
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $this->limit($perPage);
        $this->offset(($page - 1) * $perPage);
        
        $total = $this->db->count($this->modelClass::getTable(), $this->conditions);
        
        return [
            'data' => $this->get(),
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ];
    }
}