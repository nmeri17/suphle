<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Events\EmitProxy;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Events\AssignListeners;

	class UpdatefulEmitter extends SystemModelEditMock1 {

		use EmitProxy;

		const UPDATE_ERROR = "update_error";

		private $eventManager;

		public function __construct (AssignListeners $eventManager) {

			$this->eventManager = $eventManager;
		}

		/**
		 * @param {payload}:int
		*/
		public function initializeUpdateModels ( $payload):void {

			$this->payload = $payload;
		}

		public function updateModels ():OptionalDTO {

			$this->emitHelper (self::UPDATE_ERROR, $this->payload); // one of the handlers here is expected to rollback updates before it and prevent ours below from running

			return new OptionalDTO($this->payload * 3); // since event listener doesn't implement ServiceErrorCatcher, this method should terminate and return value of [failureState]
		}

		public function failureState (string $method):?OptionalDTO {

			return new OptionalDTO($this->payload);
		}
	}
?>