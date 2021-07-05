<?php

	namespace Tilwa\Tests\Modules\Cart\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Tests\Modules\Controllers\HandleCart;

	use Tilwa\Response\Format\{Markup,Json};

	use Tilwa\Contracts\Config\Router as RouterConfig;

	class BrowserRoutes extends RouteCollection {

		function __construct(CanaryValidator $validator, RouterConfig $routerConfig, SessionStorage $authStorage, MiddlewareRegistry $middlewareRegistry) {

			$this->routerConfig = $routerConfig;

			$this->canaryValidator = $validator;

			$this->authStorage = $authStorage;

			$this->middlewareRegistry = $middlewareRegistry;
		}
		
		public function _prefixCurrent() {
			
			return "cart";
		}

		public function _handlingClass ():string {

			return HandleCart::class;
		}
		
		public function crudRoutes() {
			
			return $this->_crud()->save();
		}

		public function FIRST_PATH() {

			$renderer = new Markup("handleFirstPath", "first-path");

			$flow = new ControllerFlows;

			$serviceContext = new ServiceContext(\AbsolutePath\ToModule\Services\OrderService::class, "method");

			$flow->linksTo("submit-register", $flow

				->previousResponse()->getNode("C")

				->includesPagination("path.to.next_url")
			)
			->linksTo("categories/id", $flow->previousResponse()->collectionNode("nodeD") // assumes we're coming from the category page

				->eachAttribute("key")->pipeTo(),
			)
			->linksTo("store/id", $flow->previousResponse()->collectionNode("nodeB")

				->eachAttribute("key")->oneOf()
			)
			->linksTo("orders/sort/id/id2",
				$flow->fromService(
					$serviceContext,

					$flow->previousResponse()->getNode("store.id")
				)
				->eachAttribute("key")->inRange()
			);

			return $this->_get($renderer->setFlow($flow));
		}

		public function _assignMiddleware():void {

			$this->middlewareRegistry->tagPatterns(["pattern", "pattern2"], [new Middleware]);
			
			$this->middlewareRegistry->tagPatterns(["pattern2"], [ new Middleware2]);
		}
	}
?>