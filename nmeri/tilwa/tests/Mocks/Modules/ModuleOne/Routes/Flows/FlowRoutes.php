<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Flows;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\FlowController;

	class FlowRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return FlowController::class;
		}

		public function POSTS_id () {

			$this->_get(new Json("getPostDetails"));
		}

		public function FLOW__WITH__FLOWh_id() {

			$renderer = new Json("parentFlow");

			$flow = new ControllerFlows;

			$flow->linksTo("internal-flow/id", $flow

				->previousResponse()->collectionNode("anchor")

				->eachAttribute("id")->pipeTo()
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function INTERNAL__FLOWh_id () {

			$this->_get(new Json("handleChildFlow"));
		}
	}
?>