<?php
	namespace Suphle\Adapters\Orms\Eloquent\Factories;

	use Suphle\Adapters\Orms\Eloquent\Models\EditHistory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EditHistoryFactory extends Factory {

		protected $model = EditHistory::class;

		public function definition ():array {

			return [

				"user_id" => $this->faker->randomNumber(),

				"payload" => json_encode(["foo" => "bar"])
			];
		}
	}
?>