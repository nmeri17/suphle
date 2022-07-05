<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Factories;

	use Illuminate\Database\Eloquent\Factories\Factory;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use DateTime;

	class UserFactory extends Factory {

		protected $model = User::class;

		public function definition ():array {

			return [

				"email" => $this->faker->unique()->safeEmail(),
				
				"password" => password_hash("nmeri", PASSWORD_DEFAULT),

				"email_verified_at" => new DateTime,

				"is_admin" => $this->faker->boolean()
			];
		}
	}
?>