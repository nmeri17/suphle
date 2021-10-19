<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\App\BaseInterfaceLoader;

	use Tilwa\Auth\Models\Eloquent\User;

	class UserEntityLoader extends BaseInterfaceLoader {

		public function concrete():string {

			return User::class;
		}
	}
?>