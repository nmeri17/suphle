<?php

namespace Suphle\Contracts\Database;

interface ModelSchemaDetector
{
    /**
     * True only for classes ESD knows how to interpret. Everything else
     * (plain services, ModellessPayload subclasses, value objects) is
     * explicitly out of ESD's domain and must be resolved here in RCS
     * via ordinary PHP return-type reflection instead.
     */
    public function isOrmRelevant(string $contextClass): bool;
    /**
     * Entry point for resolving full method chains right-to-left.
     * Used by RendererContentShape::resolveMethodCall and resolveStaticCall.
     * * @param string $contextClass FQCN of the root object/model (e.g., 'AppModels\Employment')
     * @param array $chain Canonical execution order of method names. Examples:
     * * Direct Static Call:
     * - Code: Employment::query()->where('active', 1)->get()
     * - Parameters: 'App\Models\Employment', ['query', 'where', 'get']
     * * Inline Instantiation:
     * - Code: $builder = new BaseEmploymentBuilder(); return $builder->getBuilder()->first();
     * - Parameters: 'Acme\Builders\BaseEmploymentBuilder', ['getBuilder', 'first']
     * * Constructor Injection Chaining:
     * - Code: return $this->employmentBuilder->getBuilder()->with('user')->paginate();
     * - Parameters: 'Acme\Builders\BaseEmploymentBuilder', ['getBuilder', 'with', 'paginate']
     * * @return array The resolved OpenAPI-style response schema descriptor.
     */
    public function resolveCallChain(string $contextClass, array $chain): array;

    /**
     * Builds an OpenAPI-style object schema for a single model class.
     */
    public function modelToSchema(string $className): array;

    /**
     * Records a model as discovered and returns its OpenAPI $ref pointer array.
     *
     * @return array{'$ref': string}
     */
    public function registerModel(string $className): array;

    /**
     * Returns the full OpenAPI component schema map for every model
     * encountered during a pass.
     */
    public function getGeneratedSchemas(): array;
}