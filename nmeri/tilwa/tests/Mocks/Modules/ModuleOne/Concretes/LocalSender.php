<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class LocalSender {

		const CASCADE_BEGIN_EVENT = "cascading";

		private $eventManager;

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function sendLocalEvent ($payload):void {

			$this->eventManager->emit(get_class(), ModuleApi::DEFAULT_EVENT, $payload);
		}

		public function sendLocalEventNoPayload ():void {

			$this->eventManager->emit(get_class(), ModuleApi::EMPTY_PAYLOAD_EVENT);
		}

		public function cascadingEntry ($payload):void {

			$this->eventManager->emit(get_class(), self::CASCADE_BEGIN_EVENT, $payload);
		}
	}
?>