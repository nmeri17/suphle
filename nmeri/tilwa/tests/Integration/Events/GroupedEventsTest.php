<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

	class GroupedEventsTest extends TestEventManager {

		protected $eventReceiverName = LocalReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		public function test_space_delimited_event_names () {

			// given => see module injection

			// then
			$this->mockEventReceiver->doNothing()->shouldBeCalled();

			$this->mockEventReceiver->unionHandler()->shouldBeCalledTimes(2);

			$this->getModuleFor(ModuleOne::class)->sendConcatEvents($this->payload); // when
		}
	}
?>