<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent\Factories;

	use Suphle\Adapters\Orms\Eloquent\Models\User;

	use Suphle\Tests\Mocks\Models\Eloquent\Employer;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EmployerFactory extends Factory {

		protected $model = Employer::class;

		public function definition ():array {

			return [

				"user_id" => User::factory()
			];
		}
	}
?>