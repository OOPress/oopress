<?php

declare(strict_types=1);

namespace OOPress\Core\Database;

use Medoo\Medoo;
use InvalidArgumentException;
use OOPress\Core\Cache\CacheManager;

abstract class Model
{
    protected static Medoo $db;
    protected static string $table;
    protected array $attributes = [];
    protected array $original = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected array $relations = [];
    
    /**
     * Get the database connection
     */
    public static function getDB(): Medoo
    {
        return static::$db;
    }
    
    /**
     * Get the table name
     */
    public static function getTable(): string
    {
        return static::$table;
    }
    
    /**
     * Set database connection for all models
     */
    public static function setDB(Medoo $db): void
    {
        static::$db = $db;
    }
    
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
     * Get cache instance
     */
    protected static function getCache(): CacheManager
    {
        static $cache = null;
        if ($cache === null) {
            $cache = new CacheManager();
        }
        return $cache;
    }
    
    /**
     * Find by ID (with cache)
     */
    public static function find(int $id): ?static
    {
        $cache = self::getCache();
        $key = static::$table . '_find_' . $id;
        
        return $cache->remember($key, function() use ($id) {
            $data = static::$db->get(static::$table, '*', ['id' => $id]);
            return $data ? new static($data) : null;
        });
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
            // Update existing record
            $result = static::$db->update(static::$table, $attributes, ['id' => $this->attributes['id']]);
            $saved = $result !== null;
            
            if ($saved) {
                self::getCache()->delete(static::$table . '_find_' . $this->attributes['id']);
                self::getCache()->delete(static::$table . '_all');
            }
            
            return $saved;
        } else {
            // Insert new record
            unset($attributes['id']);
            $result = static::$db->insert(static::$table, $attributes);
            
            // Check if result is numeric (the insert ID)
            if (is_numeric($result) && $result > 0) {
                $this->attributes['id'] = (int)$result;
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
        
        if ($result !== null) {
            // Invalidate cache
            self::getCache()->delete(static::$table . '_find_' . $this->id);
            self::getCache()->delete(static::$table . '_all');
            return true;
        }
        
        return false;
    }
    
    /**
     * Relationship: belongs to
     */
    public function belongsTo(string $related, string $foreignKey = null): ?Model
    {
        $foreignKey = $foreignKey ?? strtolower(class_basename($related)) . '_id';
        return $related::find($this->$foreignKey);
    }
    
    /**
     * Relationship: has many
     */
    public function hasMany(string $related, string $foreignKey = null): array
    {
        $foreignKey = $foreignKey ?? strtolower(class_basename(static::class)) . '_id';
        return $related::where([$foreignKey => $this->id]);
    }
    
    /**
     * Load relation
     */
    public static function with(string $relation, array $conditions = []): array
    {
        $models = static::where($conditions);
        
        foreach ($models as $model) {
            $model->loadRelation($relation);
        }
        
        return $models;
    }
    
    /**
     * Load a single relation
     */
    protected function loadRelation(string $relation): void
    {
        if (method_exists($this, $relation)) {
            $this->relations[$relation] = $this->$relation();
        }
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
        
        return array_merge($attributes, $this->relations);
    }
    
    /**
     * Get query builder
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class);
    }
    
    /**
     * Check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}