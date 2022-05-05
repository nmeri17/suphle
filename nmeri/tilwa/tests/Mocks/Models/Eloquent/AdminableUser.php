<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Tests\Mocks\Models\Eloquent\Factories\AdminUserFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class AdminableUser extends User {

		protected $table = "adminable_user";

		public function isAdmin ():bool {

			return $this->is_admin;
		}

		public static function migrationFolders ():array {

			return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
		}

		protected static function newFactory ():Factory {

			return AdminUserFactory::new();
		}
	}
?>