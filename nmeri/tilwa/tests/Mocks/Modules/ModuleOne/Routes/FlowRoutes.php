<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Flows\ControllerFlows;

	class FlowRoutes extends BrowserNoPrefix {

		public function SEGMENT() {

			$renderer = new Markup("handleFirstPath", "first-path");

			$flow = new ControllerFlows;

			$serviceContext = new ServiceContext(\AbsolutePath\ToModule\Services\OrderService::class, "method");

			$flow->linksTo("submit-register", $flow

				->previousResponse()->getNode("C")

				->includesPagination("path.to.next_url") // break each of these into their individual methods
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
	}
?>