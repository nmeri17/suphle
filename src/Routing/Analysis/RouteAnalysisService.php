<?php
namespace Suphle\Routing\Analysis;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryState, HttpMethod, PreMiddleware, Middleware, ClearMiddleware, FlowDefinition};

use Suphle\Auth\Middleware\{AuthenticateHandler, PathAuthorization};

use Suphle\Services\Decorators\ValidationRules;

use ReflectionClass, ReflectionMethod, ReflectionAttribute, Exception, RuntimeException;

/**
 * 
Tier 1: The Parent (RouteAnalysisService)

The Engine: Finds the classes, builds the URLs, identifies the methods, and finds the Payload Readers.

Tier 2: The Kids (PsalmSchemaAnalyzer & ResponseSchemaAnalyzer)

The Specialized Eyes: They look at the Return Type only. One looks at Psalm types (for docs), the other looks at Json/Markup (for testing).

Tier 3: The External Services (RouteListingService & NamedRouteReader)

The Consumers: They use the Parent/Kids to actually do something, like print a table in the console or generate a link in a view.
*/
abstract class RouteAnalysisService
{
    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly ObjectDetails $objectDetails
    ) {}

    /**
     * Requirement: Each child (Psalm or Response) must define how it 
     * perceives the final output of a method.
     */
    abstract public function getResponseShape(ReflectionMethod $method): array;

    /**
     * this is essentially same as scanAllModules but enables filters
     * * @param module, method, path
     */
    public function analyzeAll(array $filters = []): array
    {
        $allRoutes = [];
        $coordinators = $this->config->getCoordinatorClassesToScan(); // this should run each container. however this isn't our primary entry point

        foreach ($coordinators as $className) {

            $coordinatorRoutes = $this->analyzeCoordinator($className, $filters["module"] ?? "");

            foreach ($coordinatorRoutes as $route) {
            
                if ($filters["module"] && $route["module_name"] != $filters["module"]) {
                    continue;
                }
                // Filter by HTTP Method
                if (isset($filters["method"]) && strtoupper($route["method"]) !== strtoupper($filters["method"])) {
                    continue;
                }
                
                // Filter by Path segment
                if (isset($filters["path"]) && !str_contains($route["path"], $filters["path"])) {
                    continue;
                }

                $allRoutes[] = $route;
            }
        }

        return $allRoutes;
    }

    /**
     * Scans a single coordinator class for all routes.
     */
    public function analyzeCoordinator(string $coordinatorClass, string $moduleName): array
    {
        $reflection = $this->objectDetails->getReflectedClass($coordinatorClass);
        
        $prefixAttr = $reflection->getAttributes(RoutePrefix::class)[0] ?? null;
        if (!$prefixAttr) {
            throw new RuntimeException("Coordinator $coordinatorClass requires a ". RoutePrefix::class);
        }
        
        $prefixInstance = $prefixAttr->newInstance();
        
        // Extract Class-Level Defaults
        $classPreMiddleware = $this->findMiddlewareList($reflection, PreMiddleware::class);
        $classMiddleware = $this->findMiddlewareList($reflection, Middleware::class); 
        
        $canaryState = $this->getCanaryAttrs($reflection);

        $allRouteDetails = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttrs = $method->getAttributes(Route::class);
            if (empty($routeAttrs)) continue;

            $primaryRoute = $this->analyzeMethod(
                $method, 
                $routeAttrs[0], 
                $prefixInstance->prefix, 
                $canaryState, 
                $classPreMiddleware,
                $classMiddleware,
                $coordinatorClass,
                $moduleName
            );
            $allRouteDetails[] = $primaryRoute;

            // 2. Mirror Fork
            if ($prefixInstance->mirrorPrefix && !in_array($method->getName(), $prefixInstance->excludeMethods)) {
                $mirrorRoute = $primaryRoute;
                $mirrorRoute["path"] = $this->buildFullPath($prefixInstance->mirrorPrefix, $primaryRoute["path"]);
                $mirrorRoute["is_mirror"] = true;

                if ($prefixInstance->mirrorAuthenticator) {
                    // Prepend the authenticator to the already-resolved pre_middleware
                    $mirrorRoute["pre_middleware"] = array_values(array_unique(array_merge(
                        [$prefixInstance->mirrorAuthenticator], 
                        $primaryRoute["pre_middleware"]
                    )));
                }
                $allRouteDetails[] = $mirrorRoute;
            }
        }

        return $allRouteDetails;
    }

    /**
     * Deep-dives into a specific method to extract metadata.
     */
    protected function analyzeMethod(
        ReflectionMethod $method, 
        ReflectionAttribute $routeAttribute, 
        string $routePrefix, 
        ?array $canaryState, 
        array $classPreMiddleware,
        array $classMiddleware,
        string $coordinatorClass,
        string $moduleName
    ): array {
        $routeArgs = $routeAttribute->getArguments();
        
        // 1. Collect all "Explicit Negations"
        $toClear = array_map(
            fn($attr) => $attr->getArguments()[0],
            $method->getAttributes(ClearMiddleware::class)
        );

        // 2. Aggregate Pre-Middleware (Class + Method)
        $allPre = array_merge(
            $classPreMiddleware,

            $this->findMiddlewareList($method, PreMiddleware::class, $toClear)
        );
        $finalPre = array_values(array_diff(array_unique($allPre), $toClear));

        $allMidw = array_merge(
            $classMiddleware,

            $this->findMiddlewareList($method, Middleware::class, $toClear)
        );
        $finalMidw = array_values(array_diff(array_unique($allMidw), $toClear));

        return [
            "method" => ($routeArgs[1] ?? HttpMethod::GET)->value,
            "path" => $this->buildFullPath($routePrefix, $routeArgs[0] ?? ""),
            "handler" => $method->getName(),
            "middleware" => $finalMidw,
            "pre_middleware" => $finalPre, 
            "view_name" => $routeArgs[2] ?? null,
            "coordinator" => $coordinatorClass,
            "placeholders" => $this->extractPlaceholders($routeArgs[0] ?? ""),
            "validation_rules" => $this->getValidationRules($method),
            "parameters" => $this->getMethodParameters($method),
            "flows" => $this->getMethodFlows($method),
            "response_shape" => $this->getResponseShape($method),
            "module_name" => $moduleName,
            "canary_state" => $canaryState
        ];
    }

    /**
     * Inspects method parameters for Payload Readers/Builders.
     */
    protected function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $typeName = $type?->getName();

            $paramInfo = [
                "name" => $param->getName(),
                "type" => $typeName ?? "mixed",
                "required" => !$param->isOptional()
            ];

            if ($typeName && (str_contains($typeName, "Builder") || str_contains($typeName, "Reader"))) {
                $paramInfo["is_payload_reader"] = true;
                $paramInfo["payload_structure"] = $this->getPayloadStructure($typeName);
            }

            $parameters[] = $paramInfo;
        }

        return $parameters;
    }

    /**
     * Checks if a Payload class has Domain Object or Builder links.
     */
    protected function getPayloadStructure(string $payloadClass): array
    {
        try {
            if (!class_exists($payloadClass)) return [];

            $structure = [];

            if ($this->objectDetails->methodReturnType($payloadClass, "getDomainObject")) {
                $structure["has_domain_object"] = true;
                $structure["domain_class"] = $this->objectDetails->methodReturnType($payloadClass, "getDomainObject");
            }
            // Using getPublicMethods or simple method check
            if (in_array("getBuilder", $this->objectDetails->getPublicMethods($payloadClass))) {
                $structure["has_builder"] = true;
            }

            return $structure;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Scans method for #[FlowDefinition] attributes and normalizes the UnitNode structures.
     */
    protected function getMethodFlows(ReflectionMethod $method): array {
        $attributes = $method->getAttributes(FlowDefinition::class, ReflectionAttribute::IS_INSTANCEOF);
        
        return array_map(fn($attr) => $attr->newInstance(), $attributes);
    }

    protected function getRoutePrefix(ReflectionClass $reflection): string
    {
        $attr = $reflection->getAttributes(RoutePrefix::class);
        return !empty($attr) ? ($attr[0]->getArguments()[0] ?? "") : "";
    }

    protected function getCanaryAttrs(ReflectionClass $reflection): ?array
    {
        $attr = $reflection->getAttributes(CanaryState::class);
        return !empty($attr) ? ($attr[0]->getArguments() ?? null) : null;
    }

    /**
     * Scans for multiple instances of PreMiddleware attributes 
     * and returns them as [midwName => [args]]
     */
    protected function findMiddlewareList(
        ReflectionClass|ReflectionMethod $reflection,
        string $middlewareMarker,
        array $exclude = []
    ): array
    {
        $attributes = $reflection instanceof ReflectionClass ?
            $this->objectDetails->getClassAttributes($reflection->getName(), $middlewareMarker) :
            $reflection->getAttributes($middlewareMarker);
        
        $list = [];

        foreach ($attributes as $attr) {
            $instance = $attr->newInstance();
            // Index by handler class, value is the array of args
            $list[$instance->handlerClass] = $instance->args;
        }
        foreach ($exclude as $clearedHandler)
            
            unset($list[$clearedHandler]);

        return $list;
    }

    protected function getValidationRules(ReflectionMethod $method): array
    {
        $attr = $method->getAttributes(ValidationRules::class);
        return !empty($attr) ? ($attr[0]->getArguments()[0] ?? []) : [];
    }

    protected function extractPlaceholders(string $pattern): array
    {
        preg_match_all("/\{([^}]+)\}/", $pattern, $matches);
        return $matches[1] ?? [];
    }

    protected function buildFullPath(string $prefix, string $pattern): string
    {
        return "/" . trim(trim($prefix, "/") . "/" . trim($pattern, "/"), "/");
    }

    public function hasAuthBarriers (array $routeDetails):bool {

        foreach ($routeDetails["pre_middleware"] as $middleware) {

            if (
                $this->objectDetails->stringInClassTree($middleware, PathAuthorization::class) ||
                $this->objectDetails->stringInClassTree($middleware, AuthenticateHandler::class)
            )
                return true;
        }
        return false;
    }
}