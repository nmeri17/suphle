<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent\Factories;

	use Tilwa\Tests\Mocks\Models\Eloquent\{MultiEditProduct, AdminableUser};

	class MultiEditProductFactory extends NameModelFactory {

		protected $model = MultiEditProduct::class;

		public function definition ():array {

			return array_merge(parent::definition(), [

				"price" => $this->faker->numberBetween(500, 35000),

				"seller_id" => AdminableUser::factory()
			]);
		}
	}
?>