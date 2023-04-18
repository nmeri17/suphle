<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\{BaseCollection, CollectionMetaFunnel, Decorators\HandlingCoordinator};

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\{BlankMiddleware3Collector, BlankMiddleware2Collector};

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(BaseCoordinator::class)]
	class UntagsMiddleware extends BaseCollection {

		public function FIRST__UNTAGh () {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function SECOND__UNTAGh () {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function THIRD__UNTAGh () {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function RETAIN () {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function ADDITIONAL__TAGh () {

			$this->_httpGet(new Json("plainSegment"));
		}

		public function _assignMiddleware (MiddlewareRegistry $registry):void {

			$registry->tagPatterns(
				
				new BlankMiddleware2Collector(["ADDITIONAL__TAGh"])
			)
			->removeTag (
				["FIRST__UNTAGh", "SECOND__UNTAGh"], // given pattern must exist on current collection. It's only activated if one of them is intersected in full request path

				BlankMiddleware3Collector::class; // while middleware must have been tagged by a parent collection to have any effect
			);
		}
	}
?>