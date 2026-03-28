<?php

declare(strict_types=1);

namespace OOPress\Api\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * ApiRouteLoader — Loads API routes.
 * 
 * @internal
 */
class ApiRouteLoader
{
    public static function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();
        
        // Content routes
        $collection->add('api.v1.content.list', new Route(
            '/api/v1/content',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'list']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.content.create', new Route(
            '/api/v1/content',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'create']],
            methods: ['POST']
        ));
        
        $collection->add('api.v1.content.get', new Route(
            '/api/v1/content/{id}',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'get']],
            requirements: ['id' => '\d+'],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.content.update', new Route(
            '/api/v1/content/{id}',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'update']],
            requirements: ['id' => '\d+'],
            methods: ['PUT']
        ));
        
        $collection->add('api.v1.content.delete', new Route(
            '/api/v1/content/{id}',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'delete']],
            requirements: ['id' => '\d+'],
            methods: ['DELETE']
        ));
        
        $collection->add('api.v1.content.translations', new Route(
            '/api/v1/content/{id}/translations',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'getTranslations']],
            requirements: ['id' => '\d+'],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.content.publish', new Route(
            '/api/v1/content/{id}/publish',
            ['_controller' => [\OOPress\Api\Controller\ContentController::class, 'publish']],
            requirements: ['id' => '\d+'],
            methods: ['POST']
        ));
        
        // User routes
        $collection->add('api.v1.users.list', new Route(
            '/api/v1/users',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'list']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.users.create', new Route(
            '/api/v1/users',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'create']],
            methods: ['POST']
        ));
        
        $collection->add('api.v1.users.get', new Route(
            '/api/v1/users/{id}',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'get']],
            requirements: ['id' => '\d+'],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.users.update', new Route(
            '/api/v1/users/{id}',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'update']],
            requirements: ['id' => '\d+'],
            methods: ['PUT']
        ));
        
        $collection->add('api.v1.users.delete', new Route(
            '/api/v1/users/{id}',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'delete']],
            requirements: ['id' => '\d+'],
            methods: ['DELETE']
        ));
        
        $collection->add('api.v1.users.me', new Route(
            '/api/v1/users/me',
            ['_controller' => [\OOPress\Api\Controller\UserController::class, 'me']],
            methods: ['GET']
        ));
        
        // Auth routes
        $collection->add('api.v1.auth.login', new Route(
            '/api/v1/auth/login',
            ['_controller' => [\OOPress\Api\Controller\AuthController::class, 'login']],
            methods: ['POST']
        ));
        
        $collection->add('api.v1.auth.logout', new Route(
            '/api/v1/auth/logout',
            ['_controller' => [\OOPress\Api\Controller\AuthController::class, 'logout']],
            methods: ['POST']
        ));
        
        $collection->add('api.v1.auth.me', new Route(
            '/api/v1/auth/me',
            ['_controller' => [\OOPress\Api\Controller\AuthController::class, 'me']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.auth.register', new Route(
            '/api/v1/auth/register',
            ['_controller' => [\OOPress\Api\Controller\AuthController::class, 'register']],
            methods: ['POST']
        ));
        
        // Block routes
        $collection->add('api.v1.blocks.list', new Route(
            '/api/v1/blocks',
            ['_controller' => [\OOPress\Api\Controller\BlockController::class, 'list']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.blocks.regions', new Route(
            '/api/v1/blocks/regions',
            ['_controller' => [\OOPress\Api\Controller\BlockController::class, 'listRegions']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.blocks.region', new Route(
            '/api/v1/blocks/regions/{region}',
            ['_controller' => [\OOPress\Api\Controller\BlockController::class, 'getRegion']],
            methods: ['GET']
        ));
        
        $collection->add('api.v1.blocks.assign', new Route(
            '/api/v1/blocks/assign',
            ['_controller' => [\OOPress\Api\Controller\BlockController::class, 'assign']],
            methods: ['POST']
        ));
        
        $collection->add('api.v1.blocks.unassign', new Route(
            '/api/v1/blocks/assign',
            ['_controller' => [\OOPress\Api\Controller\BlockController::class, 'unassign']],
            methods: ['DELETE']
        ));
        
        return $collection;
    }
}