<?php
	namespace Suphle\Tests\Mocks\Models\Eloquent;

	use Suphle\Adapters\Orms\Eloquent\Models\User as ParentUser;

	use Suphle\Tests\Mocks\Models\Eloquent\Factories\UserFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class User extends ParentUser {

		public function isAdmin ():bool {

			return $this->is_admin;
		}

		public static function migrationFolders ():array {

			return [__DIR__ . DIRECTORY_SEPARATOR . "Migrations"];
		}

		protected static function newFactory ():Factory {

			return UserFactory::new();
		}
	}
?>