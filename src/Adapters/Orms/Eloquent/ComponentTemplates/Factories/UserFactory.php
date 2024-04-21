<?php

namespace _database_namespace\Factories;

use _database_namespace\User as EloquentUser;

use Illuminate\Database\Eloquent\Factories\Factory;

use GuidoCella\EloquentPopulator\Populator;

use DateTime;

class UserFactory extends Factory
{
    protected $model = EloquentUser::class;

    protected string $password = "nmeri";

    public function definition(): array
    {

        $guessedFields = Populator::guessFormatters($this->modelName());

        return [
            ...$guessedFields,

            "email" => $this->faker->unique()->safeEmail(),

            "password" => password_hash($this->password, PASSWORD_DEFAULT)
        ];
    }

    public function admin ():static {

        return $this->state(fn (array $attributes) => [
            "role" => "admin",
            
            "password" => password_hash($this->password, PASSWORD_DEFAULT)
        ]);
    }
}