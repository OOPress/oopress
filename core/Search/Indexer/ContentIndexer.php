<?php

declare(strict_types=1);

namespace OOPress\Search\Indexer;

use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;
use OOPress\Search\IndexableInterface;

/**
 * ContentIndexer — Makes content searchable.
 * 
 * @api
 */
class ContentIndexer implements IndexableInterface
{
    public function __construct(
        private readonly Content $content,
        private readonly ContentTranslation $translation,
    ) {}
    
    public function getSearchId(): string
    {
        return (string) $this->content->id;
    }
    
    public function getSearchType(): string
    {
        return 'content';
    }
    
    public function getSearchTitle(): string
    {
        return $this->translation->title;
    }
    
    public function getSearchContent(): string
    {
        $content = $this->translation->title . ' ' . $this->translation->body;
        
        // Add field values
        foreach ($this->translation->fields as $fieldName => $fieldValue) {
            if (is_string($fieldValue) || is_numeric($fieldValue)) {
                $content .= ' ' . $fieldValue;
            } elseif (is_array($fieldValue)) {
                $content .= ' ' . implode(' ', $fieldValue);
            }
        }
        
        return $content;
    }
    
    public function getSearchUrl(): string
    {
        return '/content/' . $this->translation->slug;
    }
    
    public function getSearchFields(): array
    {
        return [
            'content_type' => $this->content->contentType,
            'author_id' => $this->content->authorId,
            'status' => $this->content->status,
            'language' => $this->translation->language,
            'created_at' => $this->content->createdAt->format(\DateTimeInterface::ATOM),
            'updated_at' => $this->content->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
    
    public function getSearchAccess(): array
    {
        $roles = ['authenticated'];
        
        if ($this->content->status === 'published') {
            $roles[] = 'anonymous';
        }
        
        return [
            'roles' => $roles,
            'user_id' => $this->content->status === 'published' ? null : $this->content->authorId,
        ];
    }
}