<?php

declare(strict_types=1);

namespace OOPress\Core\Database;

use Medoo\Medoo;
use InvalidArgumentException;

abstract class Model
{
    protected static Medoo $db;
    protected static string $table;
    protected array $attributes = [];
    protected array $original = [];
    protected array $hidden = [];
    protected array $casts = [];
    
    /**
     * Set database connection for all models
     */
    public static function setDB(Medoo $db): void
    {
        static::$db = $db;
    }
    
    /**
     * Create new model instance
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }
    
    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }
    
    /**
     * Set attribute value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
        if (!isset($this->original[$key])) {
            $this->original[$key] = $value;
        }
    }
    
    /**
     * Get attribute value with casting
     */
    public function getAttribute(string $key)
    {
        $value = $this->attributes[$key] ?? null;
        
        if (isset($this->casts[$key])) {
            $value = $this->cast($value, $this->casts[$key]);
        }
        
        return $value;
    }
    
    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Cast value to type
     */
    private function cast($value, string $type)
    {
        return match($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => json_decode($value, true) ?? [],
            'object' => json_decode($value) ?? (object) [],
            'date' => $value,
            default => $value
        };
    }
    
    /**
     * Find by ID
     */
    public static function find(int $id): ?static
    {
        $data = static::$db->get(static::$table, '*', ['id' => $id]);
        return $data ? new static($data) : null;
    }
    
    /**
     * Find or fail
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new InvalidArgumentException(static::$table . " with id {$id} not found");
        }
        return $model;
    }
    
    /**
     * Get all records
     */
    public static function all(): array
    {
        $results = static::$db->select(static::$table, '*');
        return array_map(fn($data) => new static($data), $results);
    }
    
    /**
     * Where query
     */
    public static function where(array $conditions): array
    {
        $results = static::$db->select(static::$table, '*', $conditions);
        return array_map(fn($data) => new static($data), $results);
    }
    
    /**
     * Find first record matching conditions
     */
    public static function firstWhere(array $conditions): ?static
    {
        $data = static::$db->get(static::$table, '*', $conditions);
        return $data ? new static($data) : null;
    }
    
    /**
     * Save model to database
     */
    public function save(): bool
    {
        $attributes = $this->toArray();
        
        if (isset($this->attributes['id']) && $this->attributes['id']) {
            $result = static::$db->update(static::$table, $attributes, ['id' => $this->attributes['id']]);
            return $result !== null;
        } else {
            unset($attributes['id']);
            $id = static::$db->insert(static::$table, $attributes);
            if ($id) {
                $this->attributes['id'] = $id;
                return true;
            }
            return false;
        }
    }
    
    /**
     * Delete model
     */
    public function delete(): bool
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }
        
        $result = static::$db->delete(static::$table, ['id' => $this->attributes['id']]);
        return $result !== null;
    }
    
    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        return $attributes;
    }
    
    /**
     * Check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}