<?php
	namespace Suphle\Adapters\Orms\Eloquent\Models;

	use Suphle\Adapters\Orms\Eloquent\Factories\EditHistoryFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	abstract class EditHistory extends BaseModel {

		protected static function newFactory ():Factory {

			return EditHistoryFactory::new();
		}

		public function historical () {

			return $this->morphTo();
		}
	}
?>