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

		public function getLocalSender ():LocalSender {

			return $this->localReceiver;
		}

		public function getLocalReceiver ():LocalReceiver {

			return $this->localReceiver;
		}

		public function setBCounterValue (int $newCount):void {

			$this->bCounter->setCount($newCount);
		}

		public function getBCounterValue ():int {

			return $this->bCounter->getCount();
		}
	}
?>