<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ReadsPayloadCoordinator;

	use Suphle\Response\Format\Json;

	class PayloadCollection extends BaseCollection {

		public function _handlingClass ():string {

			return ReadsPayloadCoordinator::class;
		}

		public function ALL__PAYLOADh () {

			$this->_get(new Json("mirrorPayload"));
		}
	}
?>