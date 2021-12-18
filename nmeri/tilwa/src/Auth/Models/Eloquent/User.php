<?php
	namespace Tilwa\Auth\Models\Eloquent;

	use Illuminate\Database\Eloquent\{Model, Factories\Factory};

	use Tilwa\Auth\Models\Factories\UserFactory;

	use Tilwa\Contracts\Auth\User as UserContract;

	class User extends Model implements UserContract {

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

	    public static function __callStatic($method, $parameters) {
	        
	        return null;
	    }
	}
?>