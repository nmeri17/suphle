<?php
	namespace Tilwa\Controllers\Proxies;

	class SystemModelEditCloaker extends BaseCloakBuilder {

		public function __construct ( SystemModelCallProxy $serviceCallProxy) {

			$this->serviceCallProxy = $serviceCallProxy;
		}
	}
?>