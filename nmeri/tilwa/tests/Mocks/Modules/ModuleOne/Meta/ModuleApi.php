<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender, BCounter};

	class ModuleApi implements ModuleOne {

		private $localSender, $bCounter;

		public function __construct (LocalSender $localSender, BCounter $bCounter) {

			$this->localSender = $localSender;

			$this->bCounter = $bCounter;
		}

		public function setBCounterValue (int $newCount):void {

			$this->bCounter->setCount($newCount);
		}

		public function getBCounterValue ():int {

			return $this->bCounter->getCount();
		}

		public function noPayloadEvent ():void {

			$this->localSender->sendLocalEventNoPayload();
		}

		public function payloadEvent (int $value):void {

			$this->localSender->sendLocalEvent($value);
		}

		public function cascadeEntryEvent (int $value):void {

			$this->localSender->cascadingEntry($value);
		}

		public function sendConcatEvents (int $value):void {

			$this->localSender->sendConcatHalf($value);

			$this->localSender->sendLocalEventNoPayload();
		}
	}
?>