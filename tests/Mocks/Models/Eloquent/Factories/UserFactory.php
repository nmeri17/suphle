<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent\Factories;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Illuminate\Database\Eloquent\Factories\Factory;

	use DateTime;

	class UserFactory extends Factory {

		protected $model = EloquentUser::class;

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