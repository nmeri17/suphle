<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Request\NativeAuth; // poor location. Should be an auth namespace

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Config\{Authentication, Router};

	class AuthenticatorProvider extends ServiceProvider {

		public function bindArguments( Router $routerConfig):array {

			return [

				"isApiRoute" => $routerConfig->isApiRoute()
			];
		}

		public function concrete():string {

			return NativeAuth::class; // maybe they decide whether they want jwt, session or custom puller/hydrator
		}
	}
?>