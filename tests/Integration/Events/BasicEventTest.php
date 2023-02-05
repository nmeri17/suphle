<?php
	namespace Suphle\Tests\Integration\Events;

	use Suphle\Testing\Condiments\EmittedEventsCatcher;

	use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\LocalSender;

	class BasicEventTest extends EventTestCreator {

		use EmittedEventsCatcher;

		protected function setUp ():void {

			$this->parentSetUp();
		}

		public function test_reports_handled_events () {

			$this->getModuleOne()->noPayloadEvent(); // when

			$this->assertHandledEvent(
				LocalSender::class, ModuleOne::EMPTY_PAYLOAD_EVENT
			); // then
		}

		/*public function test_doesnt_report_unhandled_events () { // find one not sent
$this->markTestSkipped();
			$this->getModuleOne()->noPayloadEvent(); // when

			$this->assertHandledEvent(
				LocalSender::class, LocalSender::EMPTY_PAYLOAD_EVENT
			); // then
		}*/
	}
?>