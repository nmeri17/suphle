<?php
	namespace Suphle\Adapters\Orms\Eloquent\ComponentTemplates\Factories;

	use Illuminate\Database\Eloquent\Factories\Factory;

	use DateTime;

	class UserFactory extends Factory {

		// protected $model = EloquentUser::class; // connect to appropriate user

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