<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Config;

	use Suphle\Config\AscendingHierarchy;

	class FilesMock extends AscendingHierarchy {

		public function modulesNamespace ():string {

			return "Suphle\Tests\Mocks\Modules";
		}
	}
?>
