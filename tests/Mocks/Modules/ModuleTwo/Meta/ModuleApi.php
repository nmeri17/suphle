<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Suphle\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	class ModuleApi implements ModuleTwo {

		public function __construct(private readonly ModuleThree $moduleThree)
  {
  }

		public function getShallowValue ():int {

			return $this->moduleThree->getLocalValue();
		}

		public function setNestedModuleValue (int $newCount):void {

			$this->moduleThree->changeExternalValueProxy($newCount);
		}
	}
?>