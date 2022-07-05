<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Models;

	use Tilwa\Adapters\Orms\Eloquent\Factories\UserFactory;

	use Tilwa\Contracts\Auth\UserContract;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class User extends BaseModel implements UserContract {

		protected $hidden = ["password"], $table = "users",

		$guarded = ["id", "password"];

		protected static function newFactory ():Factory {

			return UserFactory::new();
		}

		public function getId () {

			return $this->id;
		}

		public function setId ($id):void {

			$this->id = $id;
		}

		public function getPassword () {

			return $this->password;
		}

		public function isAdmin ():bool {

			return $this->is_admin;
		}

		public function find ($id, $columns = ['*']) {

			return parent::find($id, $columns);
		}

		public static function migrationFolders ():array {

			return [dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Migrations"];
		}
	}
?>