<?php
	namespace Tilwa\Services\Proxies;

	class MultiUserModelEditCloaker extends BaseCloakBuilder {

		public function __construct ( MultiUserModelCallProxy $serviceCallProxy) {

			$this->serviceCallProxy = $serviceCallProxy;
		}
	}
?>