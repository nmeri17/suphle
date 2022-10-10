<?php
	namespace Suphle\Adapters\Orms\Eloquent\Models;

	use Suphle\Contracts\Services\Decorators\VariableDependencies;

	use Suphle\Adapters\Orms\Eloquent\{Condiments\MigrationLocation, Factories\EditHistoryFactory};

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EditHistory extends BaseModel implements VariableDependencies {

		use MigrationLocation;

		protected $table = "edit_history";

		protected static function newFactory ():Factory {

			return EditHistoryFactory::new();
		}

		public function historical () {

			return $this->morphTo();
		}
	}
?>