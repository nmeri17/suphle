<?php

namespace Suphle\Tests\Mocks\Models\Eloquent;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Adapters\Orms\Eloquent\{Models\BaseModel, Condiments\EditIntegrity};

use Suphle\Tests\Mocks\Models\Eloquent\Factories\EmploymentFactory;

use Illuminate\Database\Eloquent\{Factories\Factory, Relations\Relation};

class Employment extends BaseModel implements IntegrityModel
{
    use EditIntegrity;

    protected $table = "employment";

    public function edit_history(): Relation
    {

        return $this->morphMany(EditHistory::class, "historical");
    }

    public function employer()
    {

        return $this->belongsTo(Employer::class);
    }

    protected static function newFactory(): Factory
    {

        return EmploymentFactory::new();
    }

    public static function migrationFolders(): array
    {

        return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
    }
}
