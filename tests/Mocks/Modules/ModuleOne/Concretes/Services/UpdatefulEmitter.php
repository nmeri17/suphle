<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Events\EmitProxy;

	use Suphle\Contracts\Events;

	class UpdatefulEmitter extends SystemModelEditMock1 {

		use EmitProxy;

		public const UPDATE_ERROR = "update_error";

		public function __construct (protected readonly Events $eventManager) {

			//
		}

		/**
		 * @param {payload}:int
		*/
		public function initializeUpdateModels ( $payload):void {

			$this->payload = $payload;
		}

		public function updateModels ():int {

			$this->emitHelper (self::UPDATE_ERROR, $this->payload); // one of the handlers here is expected to rollback updates before it and prevent ours below from running

			return $this->payload * 3; // since event listener doesn't implement ServiceErrorCatcher, this method should terminate and return value of [failureState]
		}

		public function failureState (string $method) {

			return $this->payload;
		}
	}
?>