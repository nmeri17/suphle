<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\TestLocalReceiver;

	class GroupedEventsTest extends TestLocalReceiver {

		public function test_space_delimited_event_names () {

			$this->setMockEventReceiver([ // then

				"doNothing" => [1, []],

				"unionHandler" => [2, []]
			]); // then

			$this->parentSetUp(); // given

			$this->getModuleOne()->sendConcatEvents($this->payload); // when
		}
	}
?>