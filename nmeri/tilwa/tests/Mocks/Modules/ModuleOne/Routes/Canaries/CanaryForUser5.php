<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Tilwa\Contracts\Routing\CanaryGateway;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionForUser5;

	use Tilwa\Auth\SessionStorage;

	class CanaryForUser5 implements CanaryGateway {

		private $authStorage;

		public function __construct (SessionStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function willLoad ():bool {

			return $this->authStorage->getId() == 5;
		}

		public function entryClass ():string {

			return CollectionForUser5::class;
		}
	}
?>