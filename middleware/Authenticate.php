<?php
	
	namespace Middleware;

	use Tilwa\Routes\Middleware as TilwaMiddleware;

	class Authenticate extends TilwaMiddleware {

		/**
		* @property $postSourceBehavior
		* @property $app
		*/

		public function handle (array $args ) {

			// perform some logic here, then
			if ($this->app->user ) return true;
			
			return $this->app->router->findRoute( '401' ); // reset the route in app
			
		}
	}
?>