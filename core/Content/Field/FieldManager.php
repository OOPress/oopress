<?php

declare(strict_types=1);

namespace OOPress\Content\Field;

use Doctrine\DBAL\Connection;
use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;
use OOPress\Event\HookDispatcher;

/**
 * FieldManager — Manages field value storage and retrieval.
 * 
 * @api
 */
class FieldManager
{
    /**
     * @var array<string, FieldTypeInterface>
     */
    private array $fieldTypes = [];
    
    public function __construct(
        private readonly Connection $connection,
        private readonly HookDispatcher $hookDispatcher,
    ) {
        $this->registerCoreFieldTypes();
    }
    
    /**
     * Register core field types.
     */
    private function registerCoreFieldTypes(): void
    {
        $this->registerFieldType(new Type\TextField());
        $this->registerFieldType(new Type\TextareaField());
        $this->registerFieldType(new Type\NumberField());
        $this->registerFieldType(new Type\BooleanField());
        
        // Dispatch event for modules to register their field types
        $event = new Event\FieldTypesEvent($this);
        $this->hookDispatcher->dispatch($event, 'field_type.register');
    }
    
    /**
     * Register a field type.
     */
    public function registerFieldType(FieldTypeInterface $fieldType): void
    {
        $this->fieldTypes[$fieldType->getType()] = $fieldType;
    }
    
    /**
     * Get a field type.
     */
    public function getFieldType(string $type): ?FieldTypeInterface
    {
        return $this->fieldTypes[$type] ?? null;
    }
    
    /**
     * Get all field types.
     * 
     * @return array<string, FieldTypeInterface>
     */
    public function getAllFieldTypes(): array
    {
        return $this->fieldTypes;
    }
    
    /**
     * Save field values for a content translation.
     */
    public function saveFieldValues(ContentTranslation $translation, array $values): void
    {
        $this->connection->beginTransaction();
        
        try {
            // Delete existing values
            $this->connection->delete('oop_field_values', [
                'content_id' => $translation->contentId,
                'language' => $translation->language,
            ]);
            
            // Insert new values
            foreach ($values as $fieldName => $value) {
                $fieldDef = $this->getFieldDefinition($fieldName);
                if (!$fieldDef) {
                    continue;
                }
                
                $fieldType = $this->getFieldType($fieldDef->type);
                if (!$fieldType) {
                    continue;
                }
                
                $sanitized = $fieldType->sanitize($value, $fieldDef);
                
                $this->connection->insert('oop_field_values', [
                    'content_id' => $translation->contentId,
                    'field_id' => $this->getFieldId($fieldName),
                    'language' => $translation->language,
                    'value' => json_encode($sanitized),
                ]);
            }
            
            $this->connection->commit();
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
    
    /**
     * Validate field values.
     * 
     * @param array<string, mixed> $values Field name => value
     * @param array<FieldDefinition> $definitions Field definitions
     * @return array<string, array<string>> Validation errors keyed by field name
     */
    public function validateFields(array $values, array $definitions): array
    {
        $errors = [];
        
        foreach ($definitions as $definition) {
            $value = $values[$definition->name] ?? null;
            $fieldType = $this->getFieldType($definition->type);
            
            if (!$fieldType) {
                $errors[$definition->name][] = sprintf('Unknown field type: %s', $definition->type);
                continue;
            }
            
            $fieldErrors = $fieldType->validate($value, $definition);
            if (!empty($fieldErrors)) {
                $errors[$definition->name] = $fieldErrors;
            }
        }
        
        return $errors;
    }
    
    /**
     * Get a field definition by name.
     */
    private function getFieldDefinition(string $fieldName): ?FieldDefinition
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('oop_field_definitions')
            ->where('field_name = :name')
            ->setParameter('name', $fieldName);
        
        $row = $query->executeQuery()->fetchAssociative();
        
        if (!$row) {
            return null;
        }
        
        return FieldDefinition::fromArray($row['field_name'], [
            'type' => $row['field_type'],
            'label' => $row['label'],
            'settings' => json_decode($row['settings'], true),
            'required' => (bool) $row['required'],
            'translatable' => (bool) $row['translatable'],
            'weight' => $row['weight'],
        ]);
    }
    
    /**
     * Get field ID by name.
     */
    private function getFieldId(string $fieldName): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id')
            ->from('oop_field_definitions')
            ->where('field_name = :name')
            ->setParameter('name', $fieldName);
        
        return (int) $query->executeQuery()->fetchOne();
    }
    
    /**
     * Render a field widget.
     */
    public function renderField(string $fieldName, mixed $value, FieldDefinition $definition, array $attributes = []): string
    {
        $fieldType = $this->getFieldType($definition->type);
        
        if (!$fieldType) {
            return sprintf('<div class="error">Unknown field type: %s</div>', $definition->type);
        }
        
        return $fieldType->renderWidget($fieldName, $value, $definition, $attributes);
    }
}
