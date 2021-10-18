<?php
	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	trait IsolatedComponentSecurity {

		use SecureUserAssertions;

		protected function getContainer ():Container {

			return $this->container;
		}
	}
?>