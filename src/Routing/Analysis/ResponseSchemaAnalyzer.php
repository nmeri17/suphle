<?php
namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Json;

use Suphle\Request\PayloadStorage;

use ReflectionClass, ReflectionMethod;

// tester/router
class ResponseSchemaAnalyzer extends RouteAnalysisService
{
    use AnalyzerUtils;

    public function getResponseShape(ReflectionMethod $method): array
    {if (is_null($method->getReturnType())) var_dump($method->getName());
        $typeName = $method->getReturnType()->getName();

        if ($schema = $this->rendererTypeSummary($typeName)) return $schema;

        if (is_subclass_of($typeName, Json::class)) return [

            'type' => 'object',
            'contentMediaType' => PayloadStorage::JSON_HEADER_VALUE
        ];

        return ["type" => "object"];
    }
}