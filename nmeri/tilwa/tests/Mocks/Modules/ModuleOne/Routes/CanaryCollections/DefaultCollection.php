<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

	use Tilwa\Contracts\Routing\CanaryGateway;

	class DefaultCollection implements CanaryGateway {

		public function willLoad ():bool {

			return;
		}

		public function entryClass ():string {

			return ::class;
		}
	}
?>