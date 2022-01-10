<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class GroupedEventsTest extends TestEventManager {

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		public function test_space_delimited_event_names () {
// sendConcatHalf
			$module = $this->getModuleFor(ModuleOne::class);
// concatBindings
			$this->assertFiredEvent($module->noPayloadEvent(), ModuleOne::EMPTY_PAYLOAD_EVENT); // continue here/ see 

			// you wanna ensure handlers got called
		}
	}
?>