<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Factories\EmployerFactory;

	use Tilwa\Adapters\Orms\Eloquent\Models\BaseModel;

	class Employer extends BaseModel {

		public function employments () {

			return $this->hasMany(Employment::class);
		}

		protected static function newFactory ():Factory {

			return EmployerFactory::new();
		}

		public static function migrationFolders ():array {

			return [__DIR__ . "../Migrations"];
		}
	}
?>