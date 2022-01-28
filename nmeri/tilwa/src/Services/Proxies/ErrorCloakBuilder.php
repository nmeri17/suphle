<?php
	namespace Tilwa\Services\Proxies;

	class ErrorCloakBuilder extends BaseCloakBuilder {

		public function __construct ( ErrorCallCatchProxy $serviceCallProxy) {

			$this->serviceCallProxy = $serviceCallProxy;
		}
	}
?>