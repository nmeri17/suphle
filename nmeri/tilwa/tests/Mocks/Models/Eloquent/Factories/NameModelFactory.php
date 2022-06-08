<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Factories;

	use Illuminate\Database\Eloquent\Factories\Factory;

	abstract class NameModelFactory extends Factory {

		public function definition ():array {

			return [

				"name" => $this->faker->word()
			];
		}
	}
?>