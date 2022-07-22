<?php
	namespace Suphle\Tests\Integration\Events\BaseTypes;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

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