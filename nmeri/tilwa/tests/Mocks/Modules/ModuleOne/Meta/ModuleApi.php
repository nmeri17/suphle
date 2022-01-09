<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Events\{LocalReceiver, ReboundReceiver};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender, BCounter};

	class ModuleApi implements ModuleOne {

		private $localSender, $localReceiver, $bCounter,

		$reboundReceiver;

		public function __construct (LocalReceiver $localReceiver, LocalSender $localSender, BCounter $bCounter, ReboundReceiver $reboundReceiver) {

			$this->localSender = $localSender;

			$this->localReceiver = $localReceiver;

			$this->bCounter = $bCounter;

			$this->reboundReceiver = $reboundReceiver;
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

		public function payloadEvent (int $value):void {

			$this->localSender->sendLocalEvent($value);
		}

		public function getLocalReceivedPayload ():?int {

			$this->localReceiver->getPayload();
		}

		public function cascadeEntryEvent (int $value):void {

			$this->localSender->cascadingEntry($value);
		}

		public function cascadeFinalPayload ():?int {

			$this->reboundReceiver->getPayload();
		}
	}
?>