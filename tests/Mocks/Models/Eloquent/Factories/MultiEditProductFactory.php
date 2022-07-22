<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent\Factories;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Tests\Mocks\Models\Eloquent\MultiEditProduct;

	class MultiEditProductFactory extends NameModelFactory {

		protected $model = MultiEditProduct::class;

		public function definition ():array {

			return array_merge(parent::definition(), [

				"price" => $this->faker->numberBetween(500, 35000),

				"seller_id" => EloquentUser::factory()
			]);
		}
	}
?>