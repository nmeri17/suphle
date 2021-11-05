<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	class AuthorizeRoutes extends BrowserNoPrefix {

		// add method for authentication

		public function _assignMiddleware():void {

			$this->middlewareRegistry->tagPatterns(["pattern", "pattern2"], [new Middleware]);
			
			$this->middlewareRegistry->tagPatterns(["pattern2"], [ new Middleware2]);
		}
	}
?>