<?php

namespace Suphle\Tests\Mocks\Models\Eloquent\Factories;

use Suphle\Tests\Mocks\Models\Eloquent\{Employment, Employer};

use Illuminate\Database\Eloquent\Factories\Factory;

use Faker\Factory as FakerFactory;

class EmploymentFactory extends Factory
{
    protected $model = Employment::class;

    public function definition(): array
    {

        $faker = FakerFactory::create();

        return [

            "status" => "available",

            "employer_id" => Employer::factory(),

            "salary" => 150_000,

            "title" => $faker->jobTitle()
        ];
    }
}
