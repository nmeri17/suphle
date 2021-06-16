<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Auth\SessionStorage;

	class AuthenticatorProvider extends ServiceProvider {

		public function concrete():string {

			return SessionStorage::class;
		}
	}
?>