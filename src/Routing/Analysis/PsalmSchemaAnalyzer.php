<?php
namespace Suphle\Routing\Analysis;

use Suphle\Contracts\Config\{Router as RouterConfig};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Contracts\{PsalmCodebase, Database\ModelSchemaDetector};

use Suphle\Exception\Explosives\{Unauthenticated, UnauthorizedServiceAccess};

use Psalm\{Internal\MethodIdentifier, Type\Union};

use Psalm\Type\Atomic\{
    TInt, TFloat, TBool, TNamedObject, TGenericObject, TKeyedArray, TString
};
use ReflectionMethod;

// docs gen
class PsalmSchemaAnalyzer extends RouteAnalysisService
{
    use AnalyzerUtils, DocBlockParser;

    protected ReflectionMethod $actionMethod; // used by DocBlockParser to contruct docs

    public function __construct(
        // Parent Requirements
        RouterConfig $config,
        Container $container,
        ObjectDetails $objectDetails,

        // Analysis Dependencies
        protected readonly ModelSchemaDetector $modelDetector,
        protected readonly PsalmCodebase $psalmCodebase,
    ) {
        parent::__construct($config, $container, $objectDetails);
    }

    /**
     * Statically determines the shape by asking Psalm what the 
     * method actually returns at a type level.
     */
    public function getResponseShape(ReflectionMethod $method): array
    {
        $this->actionMethod = $method;

        $fqcn = $method->getDeclaringClass()->getName();
        
        // 1. Check for basic Markup/Redirect types first (AnalyzerUtils)
        $returnTypeString = $this->objectDetails->methodReturnType($fqcn, $method->getName());
        if ($returnTypeString) {
            $standardSchema = $this->getStandardFormatSchema($returnTypeString);
            if ($standardSchema) return $standardSchema;
        }
        $methodId = new MethodIdentifier($fqcn, $method->getName());

        // 2. Fallback to Deep Psalm Analysis for Data Renderers (Json, etc)
        $psalmReturnType = $this->psalmCodebase->getMethodAnalyzer()

        ->getMethodReturnType($methodId, $fqcn);

        if (!$psalmReturnType) return ['type' => 'object'];

        foreach ($psalmReturnType->getAtomicTypes() as $atomic) {
            if ($atomic instanceof TGenericObject && str_contains($atomic->value, 'Renderer')) {
                $dataShape = $atomic->type_params[0] ?? null;
                if ($dataShape) return $this->mapPsalmTypeToOpenApi($dataShape);
            }
            
            if ($atomic instanceof TKeyedArray) {
                return $this->mapPsalmTypeToOpenApi(new Union([$atomic]));
            }
        }

        return ['type' => 'object'];
    }

    /**
     * Converts Psalm's internal Type objects into OpenAPI Schema arrays
     */
    protected function mapPsalmTypeToOpenApi(Union $type): array
    {
        $atomicTypes = $type->getAtomicTypes();
        $atomic = reset($atomicTypes);

        if ($atomic instanceof TNamedObject && $this->modelDetector->isModel($atomic->value)) {
            return $this->modelDetector->registerModel($atomic->value);
        }

        if ($atomic instanceof TKeyedArray) {
            $properties = [];
            foreach ($atomic->properties as $key => $union) {
                $properties[$key] = $this->mapPsalmTypeToOpenApi($union);
            }
            return ['type' => 'object', 'properties' => $properties];
        }

        if ($atomic instanceof TGenericObject && $this->isCollection($atomic->value)) {
            $itemType = $atomic->type_params[count($atomic->type_params) - 1];
            return [
                'type' => 'array',
                'items' => $this->mapPsalmTypeToOpenApi($itemType)
            ];
        }

        return $this->mapPrimitive($atomic);
    }

    protected function mapPrimitive($atomic): array
    {
        return match (true) {
            $atomic instanceof TInt => ['type' => 'integer'],
            $atomic instanceof TFloat => ['type' => 'number'],
            $atomic instanceof TBool => ['type' => 'boolean'],
            $atomic instanceof TString => ['type' => 'string'],
            default => ['type' => 'string']
        };
    }

    protected function isCollection(string $fqcn): bool
    {
        $fqcn = ltrim($fqcn, '\\');
        return str_contains($fqcn, 'Collection') || 
               str_contains($fqcn, 'List') || 
               $fqcn === 'array';
    }

    public function getAuthenticationErrorMessage():string
    {
        return $this->getDiffuserMessage(Unauthenticated::class);
    }

    public function getAuthorizationErrorMessage():string
    {
        return $this->getDiffuserMessage(UnauthorizedServiceAccess::class);
    }

    protected function getDiffuserMessage (string $exceptionType):string {

        $diffuser = $this->config->getHandlers()[$exceptionType];

        return $diffuser::RAW_RESPONSE[$diffuser::ERRORS_PRESENCE];
    }
}