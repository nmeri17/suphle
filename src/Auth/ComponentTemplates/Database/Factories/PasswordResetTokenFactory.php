<?php
namespace _database_namespace_\Factories;

use _database_namespace_\{User, PasswordResetToken};

use Illuminate\Database\Eloquent\Factories\Factory;

use GuidoCella\EloquentPopulator\Populator;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<PasswordResetToken>
 */
class PasswordResetTokenFactory extends Factory {

    protected $model = PasswordResetToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {

        $guessedFields = Populator::guessFormatters($this->model);

        return [
            ...$guessedFields,
            
            // "token" => bin hex,

            "user_id" => User::inRandomOrder()->first()->id,
        ];
    }
}