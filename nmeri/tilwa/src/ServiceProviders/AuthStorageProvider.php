<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Auth\SessionStorage;

	class AuthStorageProvider extends ServiceProvider {

		public function concrete():string {

			return SessionStorage::class;
		}
	}
?>