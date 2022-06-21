<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\ReadsPayloadController;

	use Tilwa\Response\Format\Json;

	class PayloadCollection extends BaseCollection {

		public function _handlingClass ():string {

			return ReadsPayloadController::class;
		}

		public function ALL__PAYLOADh () {

			$this->_get(new Json("mirrorPayload"));
		}
	}
?>