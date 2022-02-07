<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Models;

	use Tilwa\Adapters\Orms\Eloquent\Factories\EditHistoryFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EditHistory extends BaseModel {

		protected static function newFactory ():Factory {

			return EditHistoryFactory::new();
		}

		public function historical () {

			return $this->morphTo();
		}
	}
?>