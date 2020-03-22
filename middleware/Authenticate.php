<?php
	
	namespace Middleware;

	use Tilwa\Route\Middleware as TilwaMiddleware;

	use Tilwa\Route\Route;

	class Authenticate extends TilwaMiddleware {

		/**
		* @property $postSourceBehavior
		* @property $app
		*/

		public function handle (array $args, array $requestPayload ):bool {

			$app = $this->app;

			$router = $app->router;

			if ($app->user ) return true;

			$loginPage = $router->findRoute( '/login', Route::GET );

			$router->pushPrevRequest($router->getActiveRoute(), $requestPayload ) // save current request for later

			->pushPrevRequest($loginPage, [], true )

			->setActiveRoute($loginPage); // display now

			return false;
			
		}
	}
?>