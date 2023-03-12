<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

	use Suphle\Routing\{BaseCollection, CanaryValidator, MethodSorter};

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\IntermediaryToWithout;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\BlankMiddlewareCollector;

	class MisleadingEntry extends BaseCollection {

		public function __construct(

			protected readonly CanaryValidator $canaryValidator,

			protected readonly MethodSorter $methodSorter,

			protected readonly AuthStorage $authStorage
		) {

			//
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$patterns = ["FIRST"];

			$registry->tagPatterns(new AuthenticateCollector($patterns))

			->tagPatterns(new BlankMiddlewareCollector($patterns));
		}
		
		public function FIRST () {
			
			$this->_prefixFor(IntermediaryToWithout::class);
		}
	}
?>