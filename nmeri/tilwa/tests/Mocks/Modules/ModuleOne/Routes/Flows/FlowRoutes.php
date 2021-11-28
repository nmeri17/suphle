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
	}
?>