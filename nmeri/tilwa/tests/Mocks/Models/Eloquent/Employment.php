<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Factories\EmploymentFactory;

	use Tilwa\Adapters\Orms\Eloquent\Models\BaseModel;

	class Employment extends BaseModel {

		public function employer () {

			return $this->belongsTo(Employer::class);
		}

		protected static function newFactory ():Factory {

			return EmploymentFactory::new();
		}

		public static function migrationFolders ():array {

			return [dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Migrations"];
		}
	}
?>