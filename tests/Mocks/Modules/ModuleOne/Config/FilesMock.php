<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

	use Suphle\Config\AscendingHierarchy;

	class FilesMock extends AscendingHierarchy {

		public function modulesNamespace ():string {

			return "Suphle\Tests\Mocks\Modules";
		}
	}
?>
