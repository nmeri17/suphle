<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Http\Request\NativeAuth; // poor location. Should be an auth namespace

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Config\{Authentication, Router};

	class AuthenticatorProvider extends ServiceProvider {

		public function bindArguments(Authentication $authConfig, Router $routerConfig):array {

			return [

				"userModel" => $authConfig->getUserModel(),

				"isApiRoute" => $routerConfig->isApiRoute()
			];
		}

		public function concrete():string {

			return NativeAuth::class; // maybe they decide whether they want jwt, session or custom puller/hydrator
		}
	}
?>