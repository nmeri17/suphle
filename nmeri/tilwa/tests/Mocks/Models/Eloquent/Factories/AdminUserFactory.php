<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Factories;

	use Illuminate\Database\Eloquent\Factories\Factory;

	use Tilwa\Tests\Mocks\Models\Eloquent\AdminableUser;

	use DateTime;

	class AdminUserFactory extends Factory {

		protected $model = AdminableUser::class;

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