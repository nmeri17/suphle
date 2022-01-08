<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Events\LocalReceiver;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender, BCounter};

	class ModuleApi implements ModuleOne {

		private $localSender, $localReceiver, $bCounter;

		public function __construct (LocalReceiver $localReceiver, LocalSender $localSender, BCounter $bCounter) {

			$this->localSender = $localSender;

			$this->localReceiver = $localReceiver;

			$this->bCounter = $bCounter;
		}

		public function setBCounterValue (int $newCount):void {

			$this->bCounter->setCount($newCount);
		}

		public function getBCounterValue ():int {

			return $this->bCounter->getCount();
		}

		public function noPayloadEvent ():string {

			$this->localSender->sendLocalEventNoPayload();

			return get_class($this->localSender);
		}

		public function emittedEventName ():string {

			$this->localSender->getEventName();
		}

		public function payloadEvent (int $value):void {

			$this->localSender->sendLocalEvent($value);
		}

		public function getLocalReceivedPayload ():?int {

			$this->localReceiver->getPayload();
		}
	}
?>