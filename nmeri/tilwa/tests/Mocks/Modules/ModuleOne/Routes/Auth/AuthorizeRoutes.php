<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	class AuthorizeRoutes extends BrowserNoPrefix {

		// add method for authorization

		public function _assignMiddleware():void {

			$this->middlewareRegistry->tagPatterns(["pattern", "pattern2"], [Middleware::class])
			
			->tagPatterns(["pattern2"], [ Middleware2::class])

			->removeTag (["pattern3"], [ Middleware2::class]); // ostensibly, on a prefix referencing this collection, Middleware2 was set
		}
	}
?>