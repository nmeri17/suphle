<?php

namespace Suphle\Routing\Analysis;

// tester/router

use Suphle\Response\Format\Json;
use Suphle\Contracts\Routing\ModelSchemaDetector;
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Contracts\Flows\FlowHydrator;
use ReflectionClass, ReflectionMethod;

class ResponseSchemaAnalyzer extends RouteAnalysisService
{
    use AnalyzerUtils;

    public function __construct(
        RouterConfig $config,
        Container $container,
        FlowHydrator $flowHydrator,
        protected readonly ModelSchemaDetector $modelDetector
    ) {
        parent::__construct($config, $container, $flowHydrator);
    }

    public function getResponseShape(ReflectionMethod $method): array
    {
        $returnType = $method->getReturnType();
        if (!$returnType || $returnType->isBuiltin()) return ["type" => "object"];

        $typeName = $returnType->getName();

        // 1. Standard format (HTML/Redirect) from AnalyzerUtils
        if ($schema = $this->getStandardFormatSchema($typeName)) return $schema;

        // 2. Handle Json Subclasses (Reflection-based DTO/Model detection)
        if (is_subclass_of($typeName, Json::class)) {
            $constructor = (new ReflectionClass($typeName))->getConstructor();
            
            if ($constructor && ($params = $constructor->getParameters())) {
                $firstParamType = $params[0]->getType();
                
                if ($firstParamType && !$firstParamType->isBuiltin()) {
                    $className = $firstParamType->getName();

                    // If it's a model, use the central registration system
                    if ($this->modelDetector->isModel($className)) {
                        return $this->modelDetector->registerModel($className);
                    }
                    
                    // Otherwise, just return the FQCN for later processing
                    return [
                        "type" => "object", 
                        "fqcn" => $className
                    ];
                }
            }
        }
        
        return ["type" => "object"];
    }
}