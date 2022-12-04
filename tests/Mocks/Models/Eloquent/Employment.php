<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent;

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Adapters\Orms\Eloquent\Models\{BaseModel, User};

	use Suphle\Adapters\Orms\Eloquent\Condiments\EditIntegrity;

	use Suphle\Tests\Mocks\Models\Eloquent\Factories\EmploymentFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class Employment extends BaseModel implements IntegrityModel {

		use EditIntegrity;

		protected $table = "employment";
  
  		protected $casts = [

			IntegrityModel::INTEGRITY_COLUMN => "datetime:Y-m-d H:i:s"
		];

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