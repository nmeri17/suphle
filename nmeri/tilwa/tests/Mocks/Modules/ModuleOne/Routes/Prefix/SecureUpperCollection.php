<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	class SecureUpperCollection extends BaseCollection {

		public function _authenticatedPaths():array {

			return ["PREFIX"];
		}
		
		public function PREFIX () {
			
			$this->_prefixFor(UnsecureNested::class);
		}
	}
?>