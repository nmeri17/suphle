<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\App\BaseInterfaceLoader;

	use Tilwa\Auth\Storage\SessionStorage;

	class AuthStorageLoader extends BaseInterfaceLoader {

		public function concrete():string {

			return SessionStorage::class;
		}
	}
?>