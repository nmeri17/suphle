<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\{Bootstrap, ServiceProvider};

	use Tilwa\Http\Request\NativeAuth;

	use Tilwa\Routing\RouteManager;

	class AuthenticatorProvider extends ServiceProvider {

		public function bindArguments(Bootstrap $module, RouteManager $router) {

			return [

				"userModel" => $module->getUserModel(),

				"isApiRoute" => $router->isApiRoute()
			];
		}

		public function concrete():string {

			return NativeAuth::class;
		}
	}
?>