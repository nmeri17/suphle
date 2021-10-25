<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo;

	use Tilwa\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	class ModuleApi implements ModuleTwo {

		private $moduleThree;

		public function __construct (ModuleThree $moduleThree) {

			$this->moduleThree = $moduleThree;
		}

		public function getDValueFromModuleThree ():int {

			return $this->moduleThree->getDValue();
		}
	}
?>