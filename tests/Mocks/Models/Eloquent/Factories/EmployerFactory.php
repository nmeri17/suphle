<?php

namespace Suphle\Tests\Mocks\Models\Eloquent\Factories;

use Suphle\Tests\Mocks\Models\Eloquent\{Employer, User as EloquentUser};

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{
    protected $model = Employer::class;

    public function definition(): array
    {

        return [

            "user_id" => EloquentUser::factory()
        ];
    }
}
