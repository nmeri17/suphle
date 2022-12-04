<?php
	namespace Suphle\Adapters\Orms\Eloquent\Models;

	use Suphle\Adapters\Orms\Eloquent\{Condiments\MigrationLocation, Factories\UserFactory};

	use Suphle\Contracts\{Auth\UserContract, Services\Decorators\VariableDependencies};

	use Illuminate\Database\Eloquent\Factories\Factory;

	class User extends BaseModel implements UserContract, VariableDependencies { // not a component template since it should be extended rather than overwritten

		use MigrationLocation;

		protected $hidden = ["password"];
  protected $table = "users";
  protected $guarded = ["id", "password"];

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

		public function findByPrimaryKey ($id, $columns = ['*']) {

			return $this->find($id, $columns);
		}
	}
?>