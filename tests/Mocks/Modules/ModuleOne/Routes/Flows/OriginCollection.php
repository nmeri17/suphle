<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Flows;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Flows\{ControllerFlows, Structures\ServiceContext};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\FlowController;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

	class OriginCollection extends BaseCollection {

		private $queryNodeHolder = "next_page_url";

		public function _handlingClass ():string {

			return FlowController::class;
		}

		public function COMBINE__FLOWSh() {

			$renderer = new Json("handleCombined");

			$flow = new ControllerFlows;

			$flow->linksTo("paged-data", $flow

				->previousResponse()->getNode($this->queryNodeHolder)

				->altersQuery()
			)
			->linksTo("categories/id", $flow->previousResponse()->collectionNode("data") // assumes we're coming from "/categories"

				->pipeTo(),
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function SINGLE__NODEh() {

			$renderer = new Json("handleSingleNode");

			$flow = new ControllerFlows;

			$flow->linksTo("paged-data", $flow

				->previousResponse()->getNode($this->queryNodeHolder)

				->altersQuery()
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function FROM__SERVICEh() {

			$renderer = new Json("handleFromService");

			$flow = new ControllerFlows;

			$serviceContext = new ServiceContext(FlowService::class, "customHandlePrevious");

			$flow->linksTo("orders/sort/id/id2", $flow->previousResponse()

				->collectionNode("store.id")

				->setFromService($serviceContext)

				->inRange() // has a parameterised and date variant
				// try using any other collection based method aside ranges
				// after adding them, update [flroutest->getOriginUrls]
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function PIPE__TOh() {

			$renderer = new Json("handlePipeTo");

			$flow = new ControllerFlows;

			$flow->linksTo("categories/id", $flow->previousResponse()
				->collectionNode("data") // assumes we're coming from "/categories"

				->pipeTo(),
			);
			
			$this->_get($renderer->setFlow($flow));
		}

		public function ONE__OFh() {

			$renderer = new Json("handleOneOf");

			$flow = new ControllerFlows;

			$flow->linksTo("store/id", $flow->previousResponse()->collectionNode("data", "product_name")

				->oneOf() // has a parameterised variant
			);
			
			$this->_get($renderer->setFlow($flow));
		}

		public function NO__FLOWh() {

			$this->_get(new Json("noFlowHandler"));
		}

		public function USER__CONTENTh_id () {

			$this->_get(new Json("readFlowPayload"));
		}
	}
?>