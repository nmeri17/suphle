<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\{Models\BaseModel, Condiments\EditIntegrity};

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Tests\Models\Eloquent\Factories\MultiEditProductFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class MultiEditProduct extends BaseModel implements IntegrityModel {

		use EditIntegrity;

		protected static function newFactory ():Factory {

			return MultiEditProductFactory::new();
		}

		public static function migrationFolders ():array {

			return [dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Migrations"];
		}
	}
?>