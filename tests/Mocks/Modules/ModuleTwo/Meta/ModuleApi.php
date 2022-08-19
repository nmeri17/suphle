<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Suphle\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	class ModuleApi implements ModuleTwo {

		private $moduleThree;

		public function __construct (ModuleThree $moduleThree) {

			$this->moduleThree = $moduleThree;
		}

		public function getShallowValue ():int {

			return $this->moduleThree->getLocalValue();
		}

		public function setNestedModuleValue (int $newCount):void {

			$this->moduleThree->changeExternalValueProxy($newCount);
		}
	}
?>