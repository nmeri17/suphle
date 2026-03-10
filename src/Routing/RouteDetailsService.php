<?php

namespace Suphle\Routing;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryState, HttpMethod};
use Suphle\Services\Decorators\ValidationRules;
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Coordinators\BaseCoordinator;
use Suphle\Flows\{OuterFlowWrapper, Structures\RouteUserNode};
use Suphle\Contracts\Flows\FlowHydrator;
use Suphle\Routing\Analysis\{RouteAnalysisService, RendererAnalyzerRegistry};
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;

class RouteDetailsService extends RouteAnalysisService
{
    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly FlowHydrator $flowHydrator,
        protected readonly RendererAnalyzerRegistry $rendererAnalyzerRegistry
    ) {
        parent::__construct($config, $container, $flowHydrator, $rendererAnalyzerRegistry);
    }

    public function getDetailedRouteInfo(): array
    {
        $routeDetails = [];
        $coordinatorClasses = $this->getCoordinatorClasses();

        foreach ($coordinatorClasses as $coordinatorClass) {
            $routeDetails[$coordinatorClass] = $this->analyzeCoordinator($coordinatorClass);
        }

        return $routeDetails;
    }

    protected function getCoordinatorDetailedInfo(string $coordinatorClass): array
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
                $routeArgs = $routeAttribute->getArguments();
                $routePattern = $routeArgs[0] ?? '';
                $httpMethod = $routeArgs[1] ?? HttpMethod::GET;
                $middleware = $routeArgs[2] ?? [];

                $fullPath = $this->buildFullPath($routePrefix, $routePattern);
                
                $routeDetails[] = [
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
        }

        return $routeDetails;
    }

    protected function getRoutePrefix(ReflectionClass $reflection): string
    {
        $prefixAttributes = $reflection->getAttributes(RoutePrefix::class);
        
        if (!empty($prefixAttributes)) {
            $prefixArgs = $prefixAttributes[0]->getArguments();
            return $prefixArgs[0] ?? '';
        }

        return '';
    }

    protected function getCanaryState(ReflectionClass $reflection): ?array
    {
        $canaryAttributes = $reflection->getAttributes(CanaryState::class);
        
        if (!empty($canaryAttributes)) {
            $canaryArgs = $canaryAttributes[0]->getArguments();
            return $canaryArgs[0] ?? null;
        }

        return null;
    }

    protected function getFlows(string $coordinatorClass): array
    {
        try {
            $flows = $this->flowHydrator->getFlows($coordinatorClass);
            return $flows ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

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

    protected function getValidationRules(ReflectionMethod $method): array
    {
        $validationAttributes = $method->getAttributes(ValidationRules::class);
        
        if (!empty($validationAttributes)) {
            $validationArgs = $validationAttributes[0]->getArguments();
            return $validationArgs[0] ?? [];
        }

        return [];
    }

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

    protected function getParameterType(ReflectionParameter $param): string
    {
        $type = $param->getType();
        
        if ($type instanceof ReflectionType) {
            return $type->getName();
        }
        
        return 'mixed';
    }

    protected function getPayloadStructure(string $payloadClass): array
    {
        try {
            if (!class_exists($payloadClass)) {
                return [];
            }
            
            $reflection = new ReflectionClass($payloadClass);
            $structure = [];
            
            // Look for getDomainObject method
            if ($reflection->hasMethod('getDomainObject')) {
                $method = $reflection->getMethod('getDomainObject');
                $structure['has_domain_object'] = true;
                $structure['return_type'] = $this->getMethodReturnType($method);
            }
            
            // Look for getBuilder method
            if ($reflection->hasMethod('getBuilder')) {
                $method = $reflection->getMethod('getBuilder');
                $structure['has_builder'] = true;
                $structure['builder_return_type'] = $this->getMethodReturnType($method);
            }
            
            return $structure;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getMethodReturnType(ReflectionMethod $method): string
    {
        $returnType = $method->getReturnType();
        
        if ($returnType) {
            return $returnType->getName();
        }
        
        return 'mixed';
    }

    protected function getResponseShape(ReflectionMethod $method): array
    {
        $returnType = $method->getReturnType();
        
        if (!$returnType) {
            return ['type' => 'mixed'];
        }
        
        $typeName = $returnType->getName();
        
        // Check if it's a renderer
        if (is_subclass_of($typeName, BaseRenderer::class)) {
            return [
                'type' => 'renderer',
                'renderer_class' => $typeName,
                'renderer_type' => $this->getRendererType($typeName)
            ];
        }
        
        return ['type' => $typeName];
    }

    protected function getRendererType(string $rendererClass): string
    {
        $analyzer = $this->rendererAnalyzerRegistry->getAnalyzer($rendererClass);
        
        if ($analyzer) {
            $schema = $analyzer->analyzeSchema($rendererClass, new \ReflectionMethod(__CLASS__, __METHOD__));
            return $schema['type'] ?? 'unknown';
        }
        
        return 'unknown';
    }

    protected function extractMethodSummary(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        
        if ($docComment) {
            // Extract first line of doc comment
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r\0\x0B*/");
                if (!empty($line) && !str_starts_with($line, '@')) {
                    return $line;
                }
            }
        }
        
        return ucfirst(str_replace('_', ' ', $method->getName()));
    }

    protected function extractMethodDescription(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        
        if ($docComment) {
            $lines = explode("\n", $docComment);
            $description = [];
            
            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r\0\x0B*/");
                if (!empty($line) && !str_starts_with($line, '@')) {
                    $description[] = $line;
                }
            }
            
            return implode(' ', $description);
        }
        
        return '';
    }

    protected function buildFullPath(string $prefix, string $pattern): string
    {
        $path = $prefix;
        
        if (!empty($path) && !str_starts_with($pattern, '/')) {
            $path .= '/';
        }
        
        $path .= $pattern;
        
        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        
        return $path;
    }

    protected function extractPlaceholders(string $pattern): array
    {
        $placeholders = [];
        preg_match_all('/\{([^}]+)\}/', $pattern, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $placeholder) {
                $placeholders[] = $placeholder;
            }
        }
        
        return $placeholders;
    }

    protected function getReturnType(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        
        if ($returnType) {
            return $returnType->getName();
        }
        
        return null;
    }

    public function getAllDetailedRoutes(): array
    {
        return $this->getAllRoutes();
    }

    public function getRoutesByModule(string $moduleName): array
    {
        $allRoutes = $this->getAllRoutes();
        return $this->filterRoutes($allRoutes, ['module' => $moduleName]);
    }
} 