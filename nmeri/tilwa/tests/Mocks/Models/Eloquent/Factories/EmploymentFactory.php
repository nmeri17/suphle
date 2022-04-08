<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Factories;

	use Tilwa\Tests\Mocks\Models\Eloquent\{Employment, Employer};

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EmploymentFactory extends Factory {

		protected $model = Employment::class;

		public function definition ():array {

			return [

				"status" => "available",

				"employer_id" => Employer::factory()
			];
		}
	}
?>