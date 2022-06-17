<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\TestLocalReceiver;

	class GroupedEventsTest extends TestLocalReceiver {

		protected $mockReceiverMethods = ["doNothing", "unionHandler"];

		public function test_space_delimited_event_names () {

			// given => see module injection

			$this->mockCalls([ // then

				"doNothing" => [1, []],

				"unionHandler" => [2, []]
			], $this->mockEventReceiver);

			$this->getModuleOne()->sendConcatEvents($this->payload); // when
		}
	}
?>