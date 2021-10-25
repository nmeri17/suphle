<?php
	namespace Tilwa\Auth\Models\Eloquent\Factories;

	use Illuminate\Database\Eloquent\Factories\Factory;

	use Illuminate\Support\Str;

	use Tilwa\Auth\Models\Eloquent\User;

	class UserFactory extends Factory {

		protected $model = User::class;

		public function definition ():array {

			return [

				"email" => $this->faker->unique()->safeEmail(),
				
				"password" => password_hash(Str::random(8), PASSWORD_DEFAULT)
			];
		}
	}
?>