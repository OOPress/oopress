<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Controller;

use OOPress\GraphQL\GraphQLServer;
use OOPress\Security\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GraphQLController — GraphQL endpoint controller.
 * 
 * @api
 */
class GraphQLController
{
    public function __construct(
        private readonly GraphQLServer $graphQLServer,
        private readonly UserProvider $userProvider,
    ) {}
    
    /**
     * POST /graphql
     * GraphQL endpoint.
     */
    public function graphql(Request $request): JsonResponse
    {
        // Authenticate user from session
        $user = null;
        $session = $request->getSession();
        
        if ($session && $session->has('user_id')) {
            $user = $this->userProvider->loadUserById($session->get('user_id'));
        }
        
        return $this->graphQLServer->handle($request, $user);
    }
    
    /**
     * GET /graphql/playground
     * GraphQL Playground UI.
     */
    public function playground(Request $request): Response
    {
        $html = $this->graphQLServer->playground();
        return new Response($html);
    }
    
    /**
     * GET /graphql/schema
     * GraphQL schema introspection (JSON).
     */
    public function schema(Request $request): JsonResponse
    {
        $schema = $this->graphQLServer->getSchemaJson();
        return new JsonResponse($schema);
    }
}