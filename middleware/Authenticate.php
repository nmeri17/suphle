<?php
	
	namespace Middleware;

	use Tilwa\Routes\Middleware as TilwaMiddleware;

	class Authenticate extends TilwaMiddleware {

		/**
		* @property $prevData
		* @property $app
		*/

		/**
		* @param {args}:Array. Present if there's a colon separated list of parameters passed to this middleware
		*/
		public function handle (Closure $next, ...$args ) {

			// perform some logic here, then
			if ($this->app->user ) return $next($this->prevData); // pass data to next middleware for this route through here
			
			return $this->app->router->findRoute( '401' );
			
		}
	}
?>