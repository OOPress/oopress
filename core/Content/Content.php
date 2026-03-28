<?php

declare(strict_types=1);

namespace OOPress\Content;

/**
 * Content — Base content entity.
 * 
 * This represents a piece of content regardless of its type.
 * 
 * @api
 */
class Content
{
    /**
     * @var array<string, ContentTranslation>
     */
    private array $translations = [];
    
    public function __construct(
        public readonly int $id,
        public readonly string $contentType,
        public readonly int $authorId,
        public readonly string $status,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
        public readonly ?\DateTimeImmutable $publishedAt = null,
        private ?string $currentLanguage = null,
    ) {}
    
    /**
     * Add a translation.
     */
    public function addTranslation(ContentTranslation $translation): void
    {
        $this->translations[$translation->language] = $translation;
        
        if ($translation->isDefault) {
            $this->currentLanguage = $translation->language;
        }
    }
    
    /**
     * Get a translation by language.
     */
    public function getTranslation(string $language): ?ContentTranslation
    {
        return $this->translations[$language] ?? null;
    }
    
    /**
     * Get all translations.
     * 
     * @return array<string, ContentTranslation>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
    
    /**
     * Get the best available translation for a language.
     * Falls back to default language if requested translation not available.
     */
    public function getBestTranslation(string $language): ?ContentTranslation
    {
        if (isset($this->translations[$language])) {
            return $this->translations[$language];
        }
        
        // Fall back to default language
        foreach ($this->translations as $translation) {
            if ($translation->isDefault) {
                return $translation;
            }
        }
        
        // No translations? Return the first one
        return $this->translations ? reset($this->translations) : null;
    }
    
    /**
     * Get the current language translation.
     */
    public function getCurrentTranslation(): ?ContentTranslation
    {
        if ($this->currentLanguage === null) {
            return null;
        }
        
        return $this->getTranslation($this->currentLanguage);
    }
    
    /**
     * Set the current language.
     */
    public function setCurrentLanguage(string $language): void
    {
        $this->currentLanguage = $language;
    }
    
    /**
     * Check if content is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->publishedAt !== null;
    }
    
    /**
     * Check if content is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
