<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Factories;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Tests\Mocks\Models\Eloquent\Employer;

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