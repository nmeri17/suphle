<?php
namespace Suphle\Adapters\Orms\Eloquent\Models;

use Suphle\Contracts\Database\ModelSchemaDetector;
use Suphle\Hydration\Structures\ObjectDetails;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentSchemaDetector implements ModelSchemaDetector
{
    protected array $discoveredModels = [];

    public function __construct(protected readonly ObjectDetails $objectDetails) {}

    public function isModel(string $className): bool
    {
        return is_subclass_of($className, BaseModel::class);
    }

    public function registerModel(string $className): array
    {
        $className = ltrim($className, '\\');
        if (!in_array($className, $this->discoveredModels)) {
            $this->discoveredModels[] = $className;
        }
        return ['$ref' => '#/components/schemas/' . str_replace('\\', '_', $className)];
    }

    public function modelToSchema(string $className): array
    {
        /** @var BaseModel $instance */
        $instance = $this->objectDetails->noConstructor($className);
        $conn = $instance->getConnection();
        $builder = $conn->getSchemaBuilder();
        $table = $instance->getTable();
        $hidden = $instance->getHidden();

        $properties = [];
        foreach ($builder->getColumnListing($table) as $col) {
            if (in_array($col, $hidden)) continue;
            $properties[$col] = ['type' => $this->mapDbType($builder->getColumnType($table, $col))];
        }

        // Static relationship scanning
        foreach ($this->objectDetails->getPublicMethods($className) as $method) {
            $res = $this->objectDetails->methodReturnType($className, $method);
            if ($res && is_subclass_of($res, Relation::class)) {
                $related = get_class($instance->$method()->getRelated());
                $isMany = str_contains($res, 'Many');
                
                $ref = $this->registerModel($related);
                $properties[$method] = $isMany ? ['type' => 'array', 'items' => $ref] : $ref;
            }
        }

        return ['type' => 'object', 'properties' => $properties];
    }

    public function getGeneratedSchemas(): array
    {
        $out = [];
        foreach ($this->discoveredModels as $m) {
            $out[str_replace('\\', '_', $m)] = $this->modelToSchema($m);
        }
        return $out;
    }

    protected function mapDbType(string $type): string
    {
        return match ($type) {
            'integer', 'bigint' => 'integer',
            'boolean' => 'boolean',
            'decimal', 'float' => 'number',
            default => 'string'
        };
    }
}