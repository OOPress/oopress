<?php

declare(strict_types=1);

namespace OOPress\Asset;

/**
 * AssetCompileResult — Overall result of asset compilation.
 * 
 * @api
 */
class AssetCompileResult
{
    private ?CompileResult $cssResult = null;
    private ?CompileResult $jsResult = null;
    private int $fontsCopied = 0;
    
    public function addCssResult(CompileResult $result): void
    {
        $this->cssResult = $result;
    }
    
    public function addJsResult(CompileResult $result): void
    {
        $this->jsResult = $result;
    }
    
    public function addFontCopied(): void
    {
        $this->fontsCopied++;
    }
    
    public function getCssResult(): ?CompileResult
    {
        return $this->cssResult;
    }
    
    public function getJsResult(): ?CompileResult
    {
        return $this->jsResult;
    }
    
    public function getFontsCopied(): int
    {
        return $this->fontsCopied;
    }
    
    public function isSuccess(): bool
    {
        $cssOk = $this->cssResult === null || $this->cssResult->isSuccess() || $this->cssResult->isSkipped();
        $jsOk = $this->jsResult === null || $this->jsResult->isSuccess() || $this->jsResult->isSkipped();
        
        return $cssOk && $jsOk;
    }
    
    public function getSummary(): string
    {
        $summary = [];
        
        if ($this->cssResult) {
            $summary[] = sprintf('CSS: %s', $this->cssResult->getSummary());
        }
        
        if ($this->jsResult) {
            $summary[] = sprintf('JS: %s', $this->jsResult->getSummary());
        }
        
        if ($this->fontsCopied > 0) {
            $summary[] = sprintf('Fonts: %d copied', $this->fontsCopied);
        }
        
        return implode("\n", $summary);
    }
}