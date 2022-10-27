<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Flows;

	use Suphle\Flows\ControllerFlows;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\FlowCoordinator;

	class FlowRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return FlowCoordinator::class;
		}

		public function POSTS_id () {

			$this->_get(new Json("getPostDetails"));
		}

		public function FLOW__WITH__FLOWh_id() {

			$renderer = new Json("parentFlow");

			$flow = new ControllerFlows;

			$flow->linksTo("internal-flow/id", $flow

				->previousResponse()->collectionNode("anchor")->pipeTo()
			);

			$this->_get($renderer->setFlow($flow));
		}

		public function INTERNAL__FLOWh_id () {

			$this->_get(new Json("handleChildFlow"));
		}

		public function FLOW__TO__MODULE3h () {

			$renderer = new Json("getsTenModels");

			$flow = new ControllerFlows;

			$flow->linksTo("module-three/id", $flow

				->previousResponse()->collectionNode("anchor")->pipeTo()
			);

			$this->_get($renderer->setFlow($flow));
		}
	}
?>