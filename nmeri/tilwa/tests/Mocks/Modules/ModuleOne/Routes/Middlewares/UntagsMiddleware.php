<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Middleware\MiddlewareRegistry;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddleware, BlankMiddleware2, BlankMiddleware3, BlankMiddleware4};

	use Tilwa\Response\Format\Json;

	class UntagsMiddleware extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function FIRST__UNTAGh () {

			$this->_get(new Json("plainSegment"));
		}

		public function SECOND__UNTAGh () {

			$this->_get(new Json("plainSegment"));
		}

		public function THIRD__UNTAGh () {

			$this->_get(new Json("plainSegment"));
		}

		public function RETAIN () {

			$this->_get(new Json("plainSegment"));
		}

		public function ADDITIONAL__TAGh () {

			$this->_get(new Json("plainSegment"));
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$registry->tagPatterns(
				["ADDITIONAL__TAGh"],

				[BlankMiddleware4::class]
			)
			->removeTag (
				["FIRST__UNTAGh", "SECOND__UNTAGh"], // notice that given pattern exist on current collection. It's only activated if one of them is intersected in full request path

				[ BlankMiddleware4::class] // while middleware must have been tagged by a parent collection to have any effect
			)
			->removeTag (
				["THIRD__UNTAGh"],

				[ BlankMiddleware2::class, BlankMiddleware3::class]
			);
		}
	}
?>