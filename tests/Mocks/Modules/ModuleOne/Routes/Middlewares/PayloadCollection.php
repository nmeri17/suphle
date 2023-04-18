<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ReadsPayloadCoordinator;

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(ReadsPayloadCoordinator::class)]
	class PayloadCollection extends BaseCollection {

		public function ALL__PAYLOADh () {

			$this->_httpGet(new Json("mirrorPayload"));
		}
	}
?>