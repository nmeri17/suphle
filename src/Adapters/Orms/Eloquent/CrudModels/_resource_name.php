<?php

namespace _database_namespace;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Adapters\Orms\Eloquent\{Models\BaseModel, Condiments\EditIntegrity};

use _database_namespace\Factories\_resource_nameFactory;

use Illuminate\Database\Eloquent\{Factories\Factory, Relations\Relation};

class _resource_name extends BaseModel implements IntegrityModel
{
    use EditIntegrity;

    protected $table = "_resource_name";

    public function edit_history(): Relation
    {

        return $this->morphMany(EditHistory::class, "historical");
    }

    protected static function newFactory(): Factory
    {

        return _resource_nameFactory::new();
    }

    public static function migrationFolders(): array
    {

        return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
    }
}
