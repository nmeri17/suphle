<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Tests\Mocks\Models\Eloquent\Factories\EmployerFactory;

	use Tilwa\Adapters\Orms\Eloquent\Models\BaseModel;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class Employer extends BaseModel {

		public function employments () {

			return $this->hasMany(Employment::class);
		}

		protected static function newFactory ():Factory {

			return EmployerFactory::new();
		}

		public static function migrationFolders ():array {

			return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
		}
	}
?>