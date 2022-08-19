<?php
	namespace Suphle\Tests\Integration\Events\BaseTypes;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Structures\ObjectDetails;

	use Suphle\Events\{EventManager, ModuleLevelEvents};

	use Suphle\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class EventTestCreator extends DescriptorCollection {

		use EmittedEventsCatcher;

		protected $payload = 5, $mockEventReceiver,

		$eventReceiverName;

		// since we intend to manually trigger it in extending classes
		protected function setUp ():void {}

		protected function parentSetUp ():void {

			parent::setUp();
		}

		protected function doubleEventManager ():EventManager {

			$manager = $this->positiveDouble(EventManager::class);

			$dependencies = array_map(function ($argument) {

				return $this->positiveDouble($argument);
			}, [
				ModuleOneDescriptor::class, ModuleLevelEvents::class,

				ObjectDetails::class
			]);

			$manager->setDependencies(...$dependencies);

			return $manager;
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

		protected function defaultEventManagerConstructor ():array {

			return [

				"eventManager" => $this->doubleEventManager()
			];
		}

		/**
		 * Intended to be called before [setUp]
		 * 
		 * @param {constructorStubs} Uses [defaultEventManagerConstructor] when null instead of an empty array
		*/
		protected function setMockEventReceiver (array $mockMethods, array $constructorStubs = null):void {

			$this->mockEventReceiver = $this->positiveDouble( // can't use [replaceConstructorArguments] since that requires container and that isn't available here

				$this->eventReceiverName, [],

				$mockMethods,

				$constructorStubs ?? $this->defaultEventManagerConstructor()
			);
		}

		protected function expectUpdatePayload ():array {

			return [

				"updatePayload" => [1, [$this->payload]]
			];
		}
	}
?>