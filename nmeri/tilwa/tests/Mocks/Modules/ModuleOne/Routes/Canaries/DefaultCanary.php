<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Tilwa\Contracts\Routing\CanaryGateway;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\DefaultCollection;

	class DefaultCanary implements CanaryGateway {

		public function willLoad ():bool {

			return true;
		}

		public function entryClass ():string {

			return DefaultCollection::class;
		}
	}
?>