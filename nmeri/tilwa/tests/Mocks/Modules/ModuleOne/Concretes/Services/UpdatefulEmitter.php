<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Events\{EmitProxy, EventManager};

	use Tilwa\Services\Structures\OptionalDTO;

	class UpdatefulEmitter extends SystemModelEditMock1 {

		use EmitProxy;

		const UPDATE_ERROR = "update_error";

		private $eventManager;

		public function __construct (EventManager $eventManager) {

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

			return new OptionalDTO($this->payload * 3); // stand in for database update
		}

		public function failureState (string $method):?OptionalDTO {

			return 1;
		}
	}
?>