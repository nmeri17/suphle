<?php
namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Json;
use Suphle\Contracts\{Routing\ModelSchemaDetector, Config\Router as RouterConfig};

use Suphle\Request\PayloadStorage;
use Suphle\Hydration\{Container, Structures\ObjectDetails};
use ReflectionClass, ReflectionMethod;

// tester/router
class ResponseSchemaAnalyzer extends RouteAnalysisService
{
    use AnalyzerUtils;

    public function __construct(
        // Parent Requirements
        RouterConfig $config,
        Container $container,
        ObjectDetails $objectDetails,
        //
        protected readonly ModelSchemaDetector $modelDetector
    ) {
        parent::__construct($config, $container, $objectDetails);
    }

    public function getResponseShape(ReflectionMethod $method): array
    {
        $typeName = $method->getReturnType()->getName();

        if ($schema = $this->getStandardFormatSchema($typeName)) return $schema;

        if (is_subclass_of($typeName, Json::class)) return [

            'type' => 'object',
            'contentMediaType' => PayloadStorage::JSON_HEADER_VALUE
        ];

        return ["type" => "object"];
    }
}