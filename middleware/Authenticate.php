<?php
	
	namespace Middleware;

	use Tilwa\Route\Middleware as TilwaMiddleware;

	class Authenticate extends TilwaMiddleware {

		/**
		* @property $postSourceBehavior
		* @property $app
		*/

		public function handle (array $args ):bool {

			if ($this->app->user ) return true;
			
			header('Location: /login?r=' . $this->app->getActiveRoute()->requestSlug ); // a better way: @see Get controller-line 260
			
		}
	}
?>