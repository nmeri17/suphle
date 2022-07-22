<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

	use Suphle\Routing\BaseCollection;

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\IntermediaryToWithout;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\BlankMiddleware;

	class MisleadingEntry extends BaseCollection {

		public function _authenticatedPaths ():array {

			return ["FIRST"];
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$registry->tagPatterns(["FIRST"], [BlankMiddleware::class]);
		}
		
		public function FIRST () {
			
			$this->_prefixFor(IntermediaryToWithout::class);
		}
	}
?>