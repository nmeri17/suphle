<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Tests\Mocks\Models\Eloquent\Factories\EmployerFactory;

	use Tilwa\Adapters\Orms\Eloquent\Models\{BaseModel, User};

	use Illuminate\Database\Eloquent\Factories\Factory;

	class Employer extends BaseModel {

		protected $table = "employer";

		public function employments () {

			return $this->hasMany(Employment::class);
		}

		public function user () {

			return $this->belongsTo(User::class);
		}

		protected static function newFactory ():Factory {

			return EmployerFactory::new();
		}

		public static function migrationFolders ():array {

			return array_merge(
				[__DIR__ . DIRECTORY_SEPARATOR . "Migrations"],

				User::migrationFolders()
			);
		}
	}
?>