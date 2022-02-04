<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Factories;

	use Tilwa\Adapters\Orms\Eloquent\{Models\ActiveEditors, Condiments\EditIntegrity};

	use Illuminate\Database\Eloquent\Factories\Factory;

	class MultiEditorFactory extends Factory {

		protected $model = ActiveEditors::class;

		public function definition ():array {

			return [

				EditIntegrity::INTEGRITY_COLUMN => $this->faker->numberBetween(30, 30000)
			];
		}
	}
?>