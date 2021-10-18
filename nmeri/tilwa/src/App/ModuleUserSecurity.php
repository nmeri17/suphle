<?php
	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	trait ModuleUserSecurity {

		use SecureUserAssertions;

		protected function getContainer ():Container {

			return current($this->getModules())->getContainer();
		}
	}
?>