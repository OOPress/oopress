<?php

declare(strict_types=1);

namespace OOPress\Api;

/**
 * ApiError — Standard API error structure.
 * 
 * @api
 */
class ApiError
{
    public function __construct(
        public readonly string $message,
        public readonly int $code,
        public readonly array $details = [],
    ) {}
    
    public function toArray(): array
    {
        $error = [
            'message' => $this->message,
            'code' => $this->code,
        ];
        
        if (!empty($this->details)) {
            $error['details'] = $this->details;
        }
        
        return $error;
    }
}