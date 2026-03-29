<?php

declare(strict_types=1);

namespace OOPress\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use OOPress\GraphQL\Type\QueryType;
use OOPress\GraphQL\Type\MutationType;
use OOPress\GraphQL\Type\Types;
use OOPress\Event\HookDispatcher;
use OOPress\Security\AuthorizationManager;
use OOPress\Security\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GraphQLServer — Handles GraphQL requests.
 * 
 * GDPR compliant: Self-hosted GraphQL endpoint with full access control.
 * 
 * @api
 */
class GraphQLServer
{
    private Schema $schema;
    private Types $types;
    
    public function __construct(
        private readonly HookDispatcher $hookDispatcher,
        private readonly AuthorizationManager $authorization,
        private readonly array $config = [],
    ) {
        $this->types = new Types();
        $this->initializeSchema();
    }
    
    /**
     * Initialize GraphQL schema.
     */
    private function initializeSchema(): void
    {
        $queryType = new QueryType($this->types, $this->hookDispatcher);
        $mutationType = new MutationType($this->types, $this->hookDispatcher);
        
        $schemaConfig = SchemaConfig::create()
            ->setQuery($queryType)
            ->setMutation($mutationType)
            ->setTypeLoader(fn(string $name) => $this->types->get($name));
        
        $this->schema = new Schema($schemaConfig);
        
        // Dispatch event for schema customization
        $event = new Event\SchemaBuildEvent($this->schema, $this->types);
        $this->hookDispatcher->dispatch($event, 'graphql.schema.build');
    }
    
    /**
     * Handle GraphQL request.
     */
    public function handle(Request $request, ?UserInterface $user = null): JsonResponse
    {
        // Check if GraphQL is enabled
        if (!($this->config['enabled'] ?? true)) {
            return new JsonResponse([
                'errors' => [['message' => 'GraphQL API is disabled']],
            ], 403);
        }
        
        // Parse request
        $body = json_decode($request->getContent(), true);
        $query = $body['query'] ?? null;
        $variables = $body['variables'] ?? null;
        $operation = $body['operationName'] ?? null;
        
        if (!$query) {
            return new JsonResponse([
                'errors' => [['message' => 'No query provided']],
            ], 400);
        }
        
        // Set context with user
        $context = [
            'user' => $user,
            'authorization' => $this->authorization,
            'request' => $request,
        ];
        
        // Execute query
        try {
            $debug = $this->config['debug'] ?? false;
            $result = GraphQL::executeQuery(
                $this->schema,
                $query,
                null,
                $context,
                $variables,
                $operation
            );
            
            $output = $result->toArray($debug ? DebugFlag::INCLUDE_DEBUG_MESSAGE : 0);
            
            $statusCode = isset($output['errors']) ? 400 : 200;
            return new JsonResponse($output, $statusCode);
            
        } catch (Error $e) {
            return new JsonResponse([
                'errors' => [['message' => $e->getMessage()]],
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => [['message' => 'Internal server error']],
            ], 500);
        }
    }
    
    /**
     * Get GraphQL schema as JSON (for introspection).
     */
    public function getSchemaJson(): array
    {
        return [
            'data' => GraphQL::executeQuery(
                $this->schema,
                GraphQL\Utils\BuildSchema::buildIntrospectionQuery()
            )->toArray(),
        ];
    }
    
    /**
     * Serve GraphQL Playground UI.
     */
    public function playground(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OOPress GraphQL Playground</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.28/build/static/css/index.css" />
    <script src="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.28/build/static/js/middleware.js"></script>
</head>
<body>
    <div id="root" style="height: 100vh;"></div>
    <script>
        window.addEventListener('load', function() {
            GraphQLPlayground.init(document.getElementById('root'), {
                endpoint: '/graphql',
                settings: {
                    'request.credentials': 'same-origin',
                    'schema.polling.enable': false,
                },
            });
        });
    </script>
</body>
</html>
HTML;
    }
}