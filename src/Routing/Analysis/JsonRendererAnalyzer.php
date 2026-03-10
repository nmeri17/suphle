<?php

namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Json;
use Suphle\Request\PayloadStorage;

class JsonRendererAnalyzer implements RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool
    {
        return is_subclass_of($rendererClass, Json::class);
    }
    
    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array
    {
        try {
            $reflection = new \ReflectionClass($rendererClass);
            $constructor = $reflection->getConstructor();
            
            if (!$constructor) {
                return ['type' => 'object', 'properties' => []];
            }

            $parameters = $constructor->getParameters();
            if (empty($parameters)) {
                return ['type' => 'object', 'properties' => []];
            }

            $dataParam = $parameters[0];
            $dataType = $this->getParameterType($dataParam);
            
            if ($dataType === 'array') {
                return ['type' => 'object', 'properties' => []];
            }

            if (class_exists($dataType)) {
                return $this->analyzeClassSchema($dataType);
            }

            return ['type' => $dataType];

        } catch (\Exception $e) {
            return ['type' => 'object', 'properties' => []];
        }
    }
    
    public function getContentType(string $rendererClass): string
    {
        return PayloadStorage::JSON_HEADER_VALUE;
    }
    
    private function getParameterType(\ReflectionParameter $param): string
    {
        $type = $param->getType();
        return $type ? $type->getName() : 'mixed';
    }
    
    private function analyzeClassSchema(string $className): array
    {
        // Basic schema analysis - can be extended
        return [
            'type' => 'object',
            'class' => $className,
            'properties' => []
        ];
    }
}


