<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Proxies\SecureUserAssertions;

	trait IsolatedComponentSecurity {

		use SecureUserAssertions;

		protected function getContainer ():Container {

			return $this->container;
		}
	}
?>