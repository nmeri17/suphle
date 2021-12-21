<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Tilwa\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	class ModuleApi implements ModuleTwo {

		private $moduleThree;

		public function __construct (ModuleThree $moduleThree) {

			$this->moduleThree = $moduleThree;
		}

		public function getShallowValue ():int {

			return $this->moduleThree->getLocalValue();
		}

		public function getNestedModuleValue ():int {

			return $this->moduleThree->
		}
	}
?>