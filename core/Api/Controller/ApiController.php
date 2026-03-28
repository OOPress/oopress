<?php

declare(strict_types=1);

namespace OOPress\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use OOPress\Api\ApiResponse;
use OOPress\Api\ApiError;

/**
 * ApiController — Base controller for API endpoints.
 * 
 * @api
 */
abstract class ApiController
{
    protected const API_VERSION = 'v1';
    
    /**
     * Create a successful JSON response.
     */
    protected function success(mixed $data = null, string $message = null, array $meta = []): JsonResponse
    {
        $response = new ApiResponse(
            success: true,
            data: $data,
            message: $message,
            meta: array_merge($meta, ['api_version' => self::API_VERSION])
        );
        
        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
    
    /**
     * Create an error JSON response.
     */
    protected function error(string $message, int $statusCode = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse
    {
        $response = new ApiResponse(
            success: false,
            message: $message,
            error: new ApiError($message, $statusCode, $errors)
        );
        
        return new JsonResponse($response->toArray(), $statusCode);
    }
    
    /**
     * Create a created response.
     */
    protected function created(mixed $data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        $response = new ApiResponse(
            success: true,
            data: $data,
            message: $message,
            meta: ['api_version' => self::API_VERSION]
        );
        
        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
    
    /**
     * Create a no content response.
     */
    protected function noContent(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Validate request data against rules.
     * 
     * @param array<string, mixed> $data
     * @param array<string, string|array> $rules
     * @return array<string, string> Validation errors
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }
            
            foreach ($rule as $r) {
                $error = $this->validateRule($field, $value, $r);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate a single rule.
     */
    private function validateRule(string $field, mixed $value, string $rule): ?string
    {
        if ($rule === 'required' && ($value === null || $value === '')) {
            return sprintf('%s is required', $field);
        }
        
        if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return sprintf('%s must be a valid email', $field);
        }
        
        if (str_starts_with($rule, 'min:') && !empty($value)) {
            $min = (int) substr($rule, 4);
            if (strlen((string) $value) < $min) {
                return sprintf('%s must be at least %d characters', $field, $min);
            }
        }
        
        if (str_starts_with($rule, 'max:') && !empty($value)) {
            $max = (int) substr($rule, 4);
            if (strlen((string) $value) > $max) {
                return sprintf('%s cannot exceed %d characters', $field, $max);
            }
        }
        
        return null;
    }
    
    /**
     * Get authenticated user from request.
     */
    protected function getUser(Request $request): ?\OOPress\Security\UserInterface
    {
        $token = $request->attributes->get('_token');
        
        if (!$token || !$token->getUser()) {
            return null;
        }
        
        $user = $token->getUser();
        
        if (!$user instanceof \OOPress\Security\UserInterface) {
            return null;
        }
        
        return $user;
    }
    
    /**
     * Require authentication.
     */
    protected function requireAuth(Request $request): \OOPress\Security\UserInterface
    {
        $user = $this->getUser($request);
        
        if (!$user) {
            throw new \RuntimeException('Authentication required', Response::HTTP_UNAUTHORIZED);
        }
        
        return $user;
    }
}