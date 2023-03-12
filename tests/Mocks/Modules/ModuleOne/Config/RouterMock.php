<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

	use Suphle\Config\Router;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\{ApiRoutes\V1\LowerMirror, BrowserNoPrefix};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\{BlankMiddlewareCollector, BlankMiddleware2Collector, BlankMiddleware3Collector};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\GenericMiddlewareHandler;

	class RouterMock extends Router {

		public function browserEntryRoute ():?string {

			return BrowserNoPrefix::class;
		}

		public function apiStack ():array {

			return [

				"v1" => LowerMirror::class
			];
		}

		public function mirrorsCollections ():bool {

			return true;
		}

		/**
		 * {@inheritdoc}
		*/
		public function collectorHandlers ():array {

			return array_merge(parent::collectorHandlers(), [

				BlankMiddlewareCollector::class => GenericMiddlewareHandler::class,

				BlankMiddleware2Collector::class => GenericMiddlewareHandler::class,

				BlankMiddleware3Collector::class => GenericMiddlewareHandler::class
			]);
		}
	}
?>