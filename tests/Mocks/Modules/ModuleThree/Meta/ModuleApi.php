<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	class ModuleApi implements ModuleThree {

		private $moduleOne;

		public function __construct (ModuleOne $moduleOne) {

			$this->moduleOne = $moduleOne;
		}

		public function getLocalValue ():int {

			return 10;
		}

		public function changeExternalValueProxy (int $newCount):void {

			$this->moduleOne->setBCounterValue($newCount);
		}
	}
?>