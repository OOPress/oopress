<?php

declare(strict_types=1);

namespace OOPress\Api;

/**
 * ApiResponse — Standard API response structure.
 * 
 * @api
 */
class ApiResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $message = null,
        public readonly ?ApiError $error = null,
        public readonly array $meta = [],
    ) {}
    
    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'meta' => $this->meta,
        ];
        
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }
        
        if ($this->message !== null) {
            $response['message'] = $this->message;
        }
        
        if ($this->error !== null) {
            $response['error'] = $this->error->toArray();
        }
        
        return $response;
    }
}