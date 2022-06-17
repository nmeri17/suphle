<?php
	namespace Tilwa\Tests\Integration\Events\BaseTypes;

	use Tilwa\Events\{EventManager, ModuleLevelEvents};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

	class TestLocalReceiver extends EventTestCreator {

		protected $eventReceiverName = LocalReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		protected function receiverConstructorArguments ():array {

			$manager = $this->positiveDouble(EventManager::class);

			$dependencies = array_map(function ($argument) {

				return $this->positiveDouble($argument);
			}, [ModuleOneDescriptor::class, ModuleLevelEvents::class]);

			$manager->setDependencies(...$dependencies);

			return [

				"eventManager" => $manager
			];
		}

		protected function getModuleOne ():ModuleOne {

			return $this->getModuleFor(ModuleOne::class);
		}
	}
?>