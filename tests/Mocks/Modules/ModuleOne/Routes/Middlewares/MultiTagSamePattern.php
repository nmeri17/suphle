<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Middleware\{MiddlewareRegistry, Collectors\JsonNegotiatorCollector};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\{BlankMiddlewareCollector, BlankMiddleware3Collector};

	use Suphle\Response\Format\{Json, Markup};

	#[HandlingCoordinator(BaseCoordinator::class)]
	class MultiTagSamePattern extends BaseCollection {

		public function FIRST__SINGLEh () {

			$this->_get(new Json("plainSegment"));
		}

		public function THIRD__SINGLEh () {

			$this->_get(new Json("plainSegment"));
		}

		public function FOURTH__SINGLEh () {

			$this->_prefixFor(UntagsMiddleware::class);
		}

		public function FIFTH__SINGLEh () {

			$this->_prefixFor(RetainsMiddleware::class);
		}

		public function NEGOTIATE () {

			$this->_get(new Markup("plainSegment", "generic.negotiated-content"));
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$registry->tagPatterns(
				new BlankMiddlewareCollector([

					"FIRST__SINGLEh", "FIFTH__SINGLEh"
				])
			)->tagPatterns(

				new BlankMiddleware3Collector([ "FOURTH__SINGLEh"])
			)
			->tagPatterns(new JsonNegotiatorCollector(["NEGOTIATE"]));
		}
	}
?>