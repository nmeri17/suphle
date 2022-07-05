<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Models\{BaseModel, User as EloquentUser};

	use Tilwa\Adapters\Orms\Eloquent\Condiments\EditIntegrity;

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Tests\Mocks\Models\Eloquent\Factories\MultiEditProductFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class MultiEditProduct extends BaseModel implements IntegrityModel {

		use EditIntegrity;

		protected $table = "multi_edit_product";

		protected static function newFactory ():Factory {

			return MultiEditProductFactory::new();
		}

		public static function migrationFolders ():array {

			return array_merge(
				[__DIR__ . DIRECTORY_SEPARATOR . "Migrations"],

				EloquentUser::migrationFolders()
			);
		}

		public function seller () {

			return $this->belongsTo(EloquentUser::class);
		}
	}
?>