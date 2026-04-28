<?php
namespace Suphle\Routing\Analysis;

// docs gen
use Suphle\Contracts\Config\{Router as RouterConfig, ExceptionInterceptor};
use Suphle\Hydration\Container;
use Suphle\Contracts\Flows\FlowHydrator;
use Suphle\Contracts\Routing\ModelSchemaDetector;
use Suphle\Hydration\Structures\ObjectDetails;
use Suphle\Exception\Explosives\{Unauthenticated, UnauthorizedServiceAccess};
use Psalm\Codebase;
use Psalm\Type\Union;
use Psalm\Type\Atomic\{
    TInt, TFloat, TBool, TNamedObject, TGenericObject, TKeyedArray, TString
};
use ReflectionMethod;

class PsalmSchemaAnalyzer extends RouteAnalysisService
{
    use AnalyzerUtils, DocBlockParser;

    protected ReflectionMethod $actionMethod;

    public function __construct(
        // Parent Requirements
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly FlowHydrator $flowHydrator,

        // Analysis Dependencies
        protected readonly ModelSchemaDetector $modelDetector,
        protected readonly ObjectDetails $objectDetails,
        protected readonly Codebase $psalmCodebase
        protected readonly ExceptionInterceptor $exceptionConfig
    ) {
        parent::__construct($config, $container, $flowHydrator);
    }

    /**
     * Statically determines the shape by asking Psalm what the 
     * method actually returns at a type level.
     */
    public function getResponseShape(ReflectionMethod $method): array
    {
        $this->actionMethod = $method;

        $fqcn = $method->getDeclaringClass()->getName();
        $methodId = $fqcn . '::' . $method->getName();
        
        // 1. Check for basic Markup/Redirect types first (AnalyzerUtils)
        $returnTypeString = $this->objectDetails->methodReturnType($fqcn, $method->getName());
        if ($returnTypeString) {
            $standardSchema = $this->getStandardFormatSchema($returnTypeString);
            if ($standardSchema) return $standardSchema;
        }

        // 2. Fallback to Deep Psalm Analysis for Data Renderers (Json, etc)
        $psalmReturnType = $this->psalmCodebase->methods->getMethodReturnType($methodId, $fqcn);

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