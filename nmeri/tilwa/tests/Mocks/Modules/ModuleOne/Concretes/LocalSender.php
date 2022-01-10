<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class LocalSender {

		const CASCADE_BEGIN_EVENT = "cascading";

		const CONCAT_EVENT = "concating";

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

		private function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(get_class(), $eventName, $payload);
		}
	}
?>