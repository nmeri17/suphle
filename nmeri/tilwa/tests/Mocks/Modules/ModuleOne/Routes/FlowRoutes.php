<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Response\Format\Json;

	use Tilwa\Flows\{ControllerFlows, Structures\ServiceContext};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\FlowController;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

	class FlowRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return FlowController::class;
		}

		public function COMBINE__FLOWSh() {

			$renderer = new Json("handleCombined");

			$flow = new ControllerFlows;

			$flow->linksTo("submit-register", $flow

				->previousResponse()->getNode("C")

				->includesPagination("path.to.next_url")
			)
			->linksTo("categories/id", $flow->previousResponse()->collectionNode("nodeD") // assumes we're coming from the category page

				->eachAttribute("key")->pipeTo(),
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function SINGLE__NODEh() {

			$renderer = new Json("handleSingleNode");

			$flow = new ControllerFlows;

			$flow->linksTo("submit-register", $flow

				->previousResponse()->getNode("C")

				->includesPagination("path.to.next_url")
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function FROM__SERVICEh() {

			$renderer = new Json("handleFromService");

			$flow = new ControllerFlows;

			$serviceContext = new ServiceContext(FlowService::class, "method");

			$flow->linksTo("orders/sort/id/id2",
				$flow->fromService(
					$serviceContext,

					$flow->previousResponse()->getNode("store.id")
				)
				->eachAttribute("key")->inRange() // has a parameterised variant
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function PIPE__TOh() {

			$renderer = new Json("handlePipeTo");

			$flow = new ControllerFlows;

			$flow->linksTo("categories/id", $flow->previousResponse()
				->collectionNode("nodeD") // assumes we're coming from the category page

				->eachAttribute("key")->pipeTo(),
			);
			
			$this->_get($renderer->setFlow($flow));
		}

		public function ONE__OFh() {

			$renderer = new Json("handleOneOf");

			$flow = new ControllerFlows;

			$flow->linksTo("store/id", $flow->previousResponse()->collectionNode("nodeB")

				->eachAttribute("key")->oneOf() // has a parameterised variant
			);
			
			$this->_get($renderer->setFlow($flow));
		}
	}
?>