<?php
	namespace Tilwa\Controllers\Proxies;

	class ErrorCloakBuilder extends BaseCloakBuilder {

		public function __construct ( UpdatelessCallProxy $serviceCallProxy) {

			$this->serviceCallProxy = $serviceCallProxy;
		}
	}
?>