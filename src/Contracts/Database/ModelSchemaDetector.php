<?php
namespace Suphle\Contracts\Database;

interface ModelSchemaDetector
{
    public function isModel(string $className): bool;

    /**
     * @return array{'$ref': string}|array{type: 'array', items: array}
     */
    public function registerModel(string $className): array;

    public function getGeneratedSchemas(): array;

    public function modelToSchema(string $className): array;
}