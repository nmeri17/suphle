<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Events\EventsHandler;

	class ModuleApi implements ModuleThree {

		private $moduleOne, $eventsHandler;

		public function __construct (ModuleOne $moduleOne, EventsHandler $eventsHandler) {

			$this->moduleOne = $moduleOne;

			$this->eventsHandler = $eventsHandler;
		}

		public function getLocalValue ():int {

			return 10;
		}

		public function changeExternalValueProxy (int $newCount):void {

			return $this->moduleOne->setBCounterValue($newCount);
		}

		public function getExternalReceivedPayload ():?int {

			return $this->eventsHandler->getExternalPayload();
		}
	}
?>