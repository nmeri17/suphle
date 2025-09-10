<?php

namespace Suphle\Routing\Analysis;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryState, HttpMethod};
use Suphle\Services\Decorators\ValidationRules;
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Coordinators\BaseCoordinator;
use Suphle\Flows\{OuterFlowWrapper, Structures\RouteUserNode};
use Suphle\Contracts\Flows\FlowHydrator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;

abstract class RouteAnalysisService
{
    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly FlowHydrator $flowHydrator
    ) {
        //
    }

    /**
     * Get all coordinator classes to scan for routes
     */
    protected function getCoordinatorClasses(): array
    {
        return $this->config->getCoordinatorClassesToScan();
    }

    /**
     * Analyze a single coordinator class and extract route information
     */
    protected function analyzeCoordinator(string $coordinatorClass): array
    {
        $reflection = new ReflectionClass($coordinatorClass);
        $routeDetails = [];

        // Get class-level attributes
        $routePrefix = $this->getRoutePrefix($reflection);
        $canaryState = $this->getCanaryState($reflection);
        $flows = $this->getFlows($coordinatorClass);

        // Get method-level routes
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttribute = $method->getAttributes(Route::class)[0] ?? null;
            
            if ($routeAttribute) {
                $routeDetails[] = $this->analyzeMethod($method, $routeAttribute, $routePrefix, $canaryState, $flows, $coordinatorClass);
            }
        }

        return $routeDetails;
    }

    /**
     * Analyze a single method and extract route information
     */
    protected function analyzeMethod(
        ReflectionMethod $method, 
        \ReflectionAttribute $routeAttribute, 
        string $routePrefix, 
        ?array $canaryState, 
        array $flows, 
        string $coordinatorClass
    ): array {
        $routeArgs = $routeAttribute->getArguments();
        $routePattern = $routeArgs[0] ?? '';
        $httpMethod = $routeArgs[1] ?? HttpMethod::GET;
        $middleware = $routeArgs[2] ?? [];

        $fullPath = $this->buildFullPath($routePrefix, $routePattern);
        
        return [
            'method' => $httpMethod->value,
            'path' => $fullPath,
            'handler' => $method->getName(),
            'middleware' => $middleware,
            'canary_state' => $canaryState,
            'placeholders' => $this->extractPlaceholders($routePattern),
            'renderer' => $this->getReturnType($method),
            'coordinator' => $coordinatorClass,
            'validation_rules' => $this->getValidationRules($method),
            'parameters' => $this->getMethodParameters($method),
            'flows' => $this->getMethodFlows($flows, $method->getName()),
            'response_shape' => $this->getResponseShape($method),
            'summary' => $this->extractMethodSummary($method),
            'description' => $this->extractMethodDescription($method)
        ];
    }

    /**
     * Extract route prefix from class attributes
     */
    protected function getRoutePrefix(ReflectionClass $reflection): string
    {
        $prefixAttributes = $reflection->getAttributes(RoutePrefix::class);
        
        if (!empty($prefixAttributes)) {
            $prefixArgs = $prefixAttributes[0]->getArguments();
            return $prefixArgs[0] ?? '';
        }

        return '';
    }

    /**
     * Extract canary state from class attributes
     */
    protected function getCanaryState(ReflectionClass $reflection): ?array
    {
        $canaryAttributes = $reflection->getAttributes(CanaryState::class);
        
        if (!empty($canaryAttributes)) {
            $canaryArgs = $canaryAttributes[0]->getArguments();
            return $canaryArgs[0] ?? null;
        }

        return null;
    }

    /**
     * Get flows for a coordinator class
     */
    protected function getFlows(string $coordinatorClass): array
    {
        try {
            $flows = $this->flowHydrator->getFlows($coordinatorClass);
            return $flows ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get flows for a specific method
     */
    protected function getMethodFlows(array $flows, string $methodName): array
    {
        $methodFlows = [];
        
        foreach ($flows as $flow) {
            if ($flow instanceof RouteUserNode && $flow->getMethodName() === $methodName) {
                $methodFlows[] = [
                    'type' => get_class($flow),
                    'details' => $this->extractFlowDetails($flow)
                ];
            }
        }
        
        return $methodFlows;
    }

    /**
     * Extract flow details
     */
    protected function extractFlowDetails($flow): array
    {
        if (method_exists($flow, 'getFlowDetails')) {
            return $flow->getFlowDetails();
        }
        
        return [
            'class' => get_class($flow),
            'method' => method_exists($flow, 'getMethodName') ? $flow->getMethodName() : null
        ];
    }

    /**
     * Extract validation rules from method attributes
     */
    protected function getValidationRules(ReflectionMethod $method): array
    {
        $validationAttributes = $method->getAttributes(ValidationRules::class);
        
        if (!empty($validationAttributes)) {
            $validationArgs = $validationAttributes[0]->getArguments();
            return $validationArgs[0] ?? [];
        }

        return [];
    }

    /**
     * Get method parameters with type information
     */
    protected function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $paramInfo = [
                'name' => $param->getName(),
                'type' => $this->getParameterType($param),
                'required' => !$param->isOptional(),
                'default' => $param->isOptional() ? $param->getDefaultValue() : null
            ];
            
            // Check if it's a payload reader/builder
            if ($param->getType() && !$param->getType()->isBuiltin()) {
                $typeName = $param->getType()->getName();
                if (str_contains($typeName, 'Builder') || str_contains($typeName, 'Reader')) {
                    $paramInfo['is_payload_reader'] = true;
                    $paramInfo['payload_class'] = $typeName;
                    $paramInfo['payload_structure'] = $this->getPayloadStructure($typeName);
                }
            }
            
            $parameters[] = $paramInfo;
        }
        
        return $parameters;
    }

    /**
     * Get parameter type as string
     */
    protected function getParameterType(ReflectionParameter $param): string
    {
        $type = $param->getType();
        
        if ($type instanceof ReflectionType) {
            return $type->getName();
        }
        
        return 'mixed';
    }

    /**
     * Get payload structure for ModelfulPayload classes
     */
    protected function getPayloadStructure(string $payloadClass): array
    {
        try {
            if (!class_exists($payloadClass)) {
                return [];
            }

            $reflection = new ReflectionClass($payloadClass);
            
            // Check if it extends ModelfulPayload
            if (!$reflection->isSubclassOf(\Suphle\Services\Structures\ModelfulPayload::class)) {
                return [];
            }

            // Try to get onlyFields method
            if ($reflection->hasMethod('onlyFields')) {
                $method = $reflection->getMethod('onlyFields');
                if ($method->isProtected() && $method->getDeclaringClass()->getName() === $payloadClass) {
                    $instance = $reflection->newInstanceWithoutConstructor();
                    $method->setAccessible(true);
                    return $method->invoke($instance);
                }
            }

        } catch (\Exception $e) {
            return [];
        }

        return [];
    }

    /**
     * Get method return type
     */
    protected function getReturnType(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        
        if ($returnType instanceof ReflectionType) {
            return $returnType->getName();
        }
        
        return null;
    }

    /**
     * Get response shape information
     */
    protected function getResponseShape(ReflectionMethod $method): array
    {
        $returnType = $this->getReturnType($method);
        
        if (!$returnType) {
            return ['type' => 'unknown'];
        }

        $rendererType = $this->getRendererType($returnType);
        
        return [
            'type' => $rendererType,
            'renderer_class' => $returnType,
            'renderer_type' => $rendererType
        ];
    }

    /**
     * Determine renderer type from class name
     */
    protected function getRendererType(string $rendererClass): string
    {
        if (str_contains($rendererClass, 'Json')) {
            return 'json';
        }
        
        if (str_contains($rendererClass, 'Markup')) {
            return 'html';
        }
        
        if (str_contains($rendererClass, 'Redirect')) {
            return 'redirect';
        }
        
        if (str_contains($rendererClass, 'Stream')) {
            return 'stream';
        }
        
        return 'unknown';
    }

    /**
     * Extract method summary from docblock
     */
    protected function extractMethodSummary(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        
        if (!$docComment) {
            return ucfirst($method->getName());
        }

        // Extract first line of docblock
        $lines = explode("\n", $docComment);
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (!empty($line) && !str_starts_with($line, '@')) {
                return $line;
            }
        }

        return ucfirst($method->getName());
    }

    /**
     * Extract method description from docblock
     */
    protected function extractMethodDescription(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        
        if (!$docComment) {
            return '';
        }

        $lines = explode("\n", $docComment);
        $description = [];
        
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (empty($line) || str_starts_with($line, '@')) {
                break;
            }
            $description[] = $line;
        }

        return implode(' ', $description);
    }

    /**
     * Build full path from prefix and pattern
     */
    protected function buildFullPath(string $prefix, string $pattern): string
    {
        if (empty($prefix)) {
            return '/' . ltrim($pattern, '/');
        }

        if (empty($pattern)) {
            return '/' . ltrim($prefix, '/');
        }

        return '/' . ltrim($prefix, '/') . '/' . ltrim($pattern, '/');
    }

    /**
     * Extract placeholders from route pattern
     */
    protected function extractPlaceholders(string $pattern): array
    {
        preg_match_all('/\{([^}]+)\}/', $pattern, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Get all routes from all coordinators
     */
    protected function getAllRoutes(): array
    {
        $allRoutes = [];
        $coordinatorClasses = $this->getCoordinatorClasses();

        foreach ($coordinatorClasses as $coordinatorClass) {
            $routes = $this->analyzeCoordinator($coordinatorClass);
            $allRoutes = array_merge($allRoutes, $routes);
        }

        return $allRoutes;
    }

    /**
     * Filter routes by various criteria
     */
    protected function filterRoutes(array $routes, array $filters = []): array
    {
        return array_filter($routes, function ($route) use ($filters) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'method':
                            if ($route['method'] !== $value) {
                                return false;
                            }
                            break;
                        case 'module':
                            $moduleName = explode('\\', $route['coordinator'])[1] ?? '';
                            if ($moduleName !== $value) {
                                return false;
                            }
                            break;
                        case 'path':
                            if (!str_contains($route['path'], $value)) {
                                return false;
                            }
                            break;
                    }
                }
            }
            return true;
        });
    }
} 