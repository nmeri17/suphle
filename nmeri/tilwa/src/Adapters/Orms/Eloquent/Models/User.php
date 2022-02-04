<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Models;

	use Tilwa\Adapters\Orms\Eloquent\Factories\UserFactory;

	use Tilwa\Contracts\Auth\UserContract;

	class User extends BaseModel implements UserContract {

		protected $hidden = ["password"], $table = "users",

		$fillable = ["email", "password"];

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
	}
?>