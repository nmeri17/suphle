<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\BaseCollection;

	use Suphle\Middleware\{MiddlewareRegistry, Handlers\JsonNegotiator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddleware, BlankMiddleware2, BlankMiddleware3, BlankMiddleware4};

	use Suphle\Response\Format\{Json, Markup};

	class MultiTagSamePattern extends BaseCollection {

		public function _handlingClass ():string {

			return BaseCoordinator::class;
		}

		public function FIRST__SINGLEh () {

			$this->_get(new Json("plainSegment"));
		}

		public function SECOND__SINGLEh () {

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

			$this->_get(new Markup("plainSegment", "generic/negotiated-content"));
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$registry->tagPatterns(
				["FIRST__SINGLEh", "SECOND__SINGLEh", "FIFTH__SINGLEh"],

				[BlankMiddleware::class]
			)
			->tagPatterns(
				["SECOND__SINGLEh"],

				[ BlankMiddleware2::class]
			)
			->tagPatterns(
				["THIRD__SINGLEh"],

				[ BlankMiddleware3::class, BlankMiddleware4::class]
			)
			->tagPatterns(
				["FOURTH__SINGLEh"],

				[ BlankMiddleware2::class, BlankMiddleware4::class, BlankMiddleware3::class]
			)
			->tagPatterns(["NEGOTIATE"], [JsonNegotiator::class]);
		}
	}
?>