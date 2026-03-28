<?php

declare(strict_types=1);

namespace OOPress\Search;

/**
 * SearchQuery — Search query parameters.
 * 
 * @api
 */
class SearchQuery
{
    private string $keyword = '';
    private int $limit = 20;
    private int $offset = 0;
    private array $filters = [];
    private array $facets = [];
    private string $sortBy = 'relevance';
    private string $sortOrder = 'desc';
    private array $types = [];
    private ?string $language = null;
    private ?array $userRoles = null;
    private ?int $userId = null;
    
    public function setKeyword(string $keyword): self
    {
        $this->keyword = trim($keyword);
        return $this;
    }
    
    public function getKeyword(): string
    {
        return $this->keyword;
    }
    
    public function setLimit(int $limit): self
    {
        $this->limit = min(max($limit, 1), 100);
        return $this;
    }
    
    public function getLimit(): int
    {
        return $this->limit;
    }
    
    public function setOffset(int $offset): self
    {
        $this->offset = max($offset, 0);
        return $this;
    }
    
    public function getOffset(): int
    {
        return $this->offset;
    }
    
    public function addFilter(string $field, mixed $value): self
    {
        $this->filters[$field] = $value;
        return $this;
    }
    
    public function getFilters(): array
    {
        return $this->filters;
    }
    
    public function addFacet(string $field): self
    {
        if (!in_array($field, $this->facets)) {
            $this->facets[] = $field;
        }
        return $this;
    }
    
    public function getFacets(): array
    {
        return $this->facets;
    }
    
    public function setSortBy(string $sortBy): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }
    
    public function getSortBy(): string
    {
        return $this->sortBy;
    }
    
    public function setSortOrder(string $sortOrder): self
    {
        $this->sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        return $this;
    }
    
    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }
    
    public function addType(string $type): self
    {
        if (!in_array($type, $this->types)) {
            $this->types[] = $type;
        }
        return $this;
    }
    
    public function getTypes(): array
    {
        return $this->types;
    }
    
    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }
    
    public function getLanguage(): ?string
    {
        return $this->language;
    }
    
    public function setUserRoles(array $roles): self
    {
        $this->userRoles = $roles;
        return $this;
    }
    
    public function getUserRoles(): ?array
    {
        return $this->userRoles;
    }
    
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getUserId(): ?int
    {
        return $this->userId;
    }
    
    /**
     * Create from request.
     */
    public static function fromRequest(array $params): self
    {
        $query = new self();
        
        if (isset($params['q'])) {
            $query->setKeyword($params['q']);
        }
        
        if (isset($params['limit'])) {
            $query->setLimit((int) $params['limit']);
        }
        
        if (isset($params['page'])) {
            $page = (int) $params['page'];
            $limit = $query->getLimit();
            $query->setOffset(($page - 1) * $limit);
        }
        
        if (isset($params['type'])) {
            foreach ((array) $params['type'] as $type) {
                $query->addType($type);
            }
        }
        
        if (isset($params['sort'])) {
            $query->setSortBy($params['sort']);
        }
        
        if (isset($params['order'])) {
            $query->setSortOrder($params['order']);
        }
        
        return $query;
    }
}