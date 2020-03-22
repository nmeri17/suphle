<?php
	
	namespace Middleware;

	use Tilwa\Route\Middleware as TilwaMiddleware;

	use Tilwa\Route\Route;

	class NoAccount extends TilwaMiddleware {

		/**
		* @property $postSourceBehavior
		* @property $app
		*/

		public function handle (array $args, array $requestPayload ):bool {

			$app = $this->app;

			$router = $app->router;
//var_dump($app->user); die();
			if (!$app->user ) return true;

			$userPage = $router->findRoute( '/profile', Route::GET );

			$router->pushPrevRequest($userPage, $requestPayload )

			->setActiveRoute($userPage);

			return false;
			
		}
	}
?>