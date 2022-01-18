<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Events\{EmitProxy, EventManager};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class LocalSender {

		use EmitProxy;

		const CASCADE_BEGIN_EVENT = "cascading";

		const CONCAT_EVENT = "concating";

		const CASCADE_EXTERNAL_BEGIN_EVENT = "begin_external_cascade";

		private $eventManager;

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function sendLocalEvent ($payload):void {

			$this->emitHelper (ModuleApi::DEFAULT_EVENT, $payload);
		}

		public function sendLocalEventNoPayload ():void {

			$this->emitHelper (ModuleApi::EMPTY_PAYLOAD_EVENT);
		}

		public function cascadingEntry ($payload):void {

			$this->emitHelper (self::CASCADE_BEGIN_EVENT, $payload);
		}

		public function sendConcatHalf ($payload):void {

			$this->emitHelper (self::CONCAT_EVENT, $payload);
		}

		public function beginExternalCascade ($payload):void {

			$this->emitHelper (self::CASCADE_EXTERNAL_BEGIN_EVENT, $payload);
		}
	}
?>