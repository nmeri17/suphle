<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent;

	use Suphle\Adapters\Orms\Eloquent\Models\{BaseModel, User as EloquentUser};

	use Suphle\Adapters\Orms\Eloquent\Condiments\EditIntegrity;

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Tests\Mocks\Models\Eloquent\Factories\MultiEditProductFactory;

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