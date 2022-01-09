<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Tilwa\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Events\ExternalReactor;

	class ModuleApi implements ModuleTwo {

		private $moduleThree, $externalReactor;

		public function __construct (ModuleThree $moduleThree, ExternalReactor $externalReactor) {

			$this->moduleThree = $moduleThree;

			$this->externalReactor = $externalReactor;
		}

		public function getShallowValue ():int {

			return $this->moduleThree->getLocalValue();
		}

		public function setNestedModuleValue ():void {

			$this->moduleThree->changeExternalValueProxy($this->newExternalValue());
		}

		public function newExternalValue ():int {

			return 67;
		}

		public function decoupledExternalReceivedPayload ():?int {

			return $this->externalReactor->getPayload();
		}
	}
?>