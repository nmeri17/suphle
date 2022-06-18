<?php
	namespace Tilwa\Tests\Integration\Events\BaseTypes;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

	class TestLocalReceiver extends EventTestCreator {

		protected $eventReceiverName = LocalReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		protected function getModuleOne ():ModuleOne {

			return $this->getModuleFor(ModuleOne::class);
		}
	}
?>