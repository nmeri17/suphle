<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\App\Container;

	use Tilwa\Testing\Proxies\SecureUserAssertions;

	trait ModuleUserSecurity {

		use SecureUserAssertions;

		protected function getContainer ():Container {

			return current($this->getModules())->getContainer();
		}
	}
?>