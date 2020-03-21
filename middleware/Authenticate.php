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

			$destination = $router->findRoute( '/login', Route::GET );

			$router->pushPrevRequest($destination, $requestPayload, true )

			->setActiveRoute($destination);

			return false;
			
		}
	}
?>