<?php

namespace Suphle\Tests\Mocks\Models\Eloquent;

use Suphle\Tests\Mocks\Models\Eloquent\Factories\EmployerFactory;

use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

use Illuminate\Database\Eloquent\Factories\Factory;

class Employer extends BaseModel
{
    protected $table = "employer";

    public function employments()
    {

        return $this->hasMany(Employment::class);
    }

    public function user()
    {

        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {

        return EmployerFactory::new();
    }

    public static function migrationFolders(): array
    {

        return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
    }
}
