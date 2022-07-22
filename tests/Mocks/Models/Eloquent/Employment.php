<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent;

	use Suphle\Adapters\Orms\Eloquent\Models\{BaseModel, User};

	use Suphle\Tests\Mocks\Models\Eloquent\Factories\EmploymentFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class Employment extends BaseModel {

		protected $table = "employment";

		public function employer () {

			return $this->belongsTo(Employer::class);
		}

		protected static function newFactory ():Factory {

			return EmploymentFactory::new();
		}

		public static function migrationFolders ():array {

			return array_merge(
				[__DIR__ . DIRECTORY_SEPARATOR . "Migrations"],

				User::migrationFolders()
			);
		}
	}
?>