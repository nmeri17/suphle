<?php
	namespace _database_namespace;

	use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

	use _database_namespace\Factories\EditHistoryFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class EditHistory extends BaseModel {

		protected $table = "edit_history";

		public function historical () {

			return $this->morphTo();
		}

		protected static function newFactory ():Factory {

			return EditHistoryFactory::new();
		}

		public static function migrationFolders ():array {

			return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
		}
	}
?>