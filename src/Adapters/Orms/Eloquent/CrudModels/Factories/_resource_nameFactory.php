<?php

namespace _database_namespace\_resource_name\Factories;

use _database_namespace\_resource_name;

use Illuminate\Database\Eloquent\Factories\Factory;

use Faker\Factory as FakerFactory;

class _resource_nameFactory extends Factory
{
    protected $model = _resource_name::class;

    public function definition(): array
    {

        $faker = FakerFactory::create();

        return [

            "title" => $faker->name()
        ];
    }
}
