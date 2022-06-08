<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

	use Tilwa\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	class TestEventManager extends DescriptorCollection {

		use EmittedEventsCatcher;

		protected $payload = 5, $mockEventReceiver,

		$eventReceiverName;

		public function setUp ():void {

			if (!is_null($this->eventReceiverName))

				$this->mockEventReceiver = $this->positiveDouble($this->eventReceiverName);

			parent::setUp();
		}

		/**
		 * The receiver, [eventReceiverName], will be replaced in the listening module with a mock allowing us know whether it actually handled event
		 * 
		 * @param {descriptorName}: The module receiving the event to be emitted
		 * 
		 * @return new module with updates
		*/
		protected function replicatorProxy (string $descriptorName):ModuleDescriptor {

			return $this->replicateModule($descriptorName, function(WriteOnlyContainer $container) {

				$container->replaceWithConcrete($this->eventReceiverName, $this->mockEventReceiver);
			});
		}

		protected function expectUpdatePayload ():void {

			$this->mockCalls([

				"updatePayload" => [1, [$this->payload]]
			], $this->mockEventReceiver);
		}
	}
?>