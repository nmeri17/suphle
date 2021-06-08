<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Http\Request\NativeAuth; // poor location. Should be an auth namespace

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Config\Authentication;

	class AuthenticatorProvider extends ServiceProvider {

		public function bindArguments(Authentication $config, RouteManager $router):array {

			return [

				"userModel" => $config->getUserModel(),

				"isApiRoute" => $router->isApiRoute()
			];
		}

		public function concrete():string {

			return NativeAuth::class; // maybe they decide whether they want jwt, session or custom puller/hydrator
		}
	}
?>