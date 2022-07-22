<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

	use Suphle\Contracts\{Routing\CanaryGateway, Auth\AuthStorage};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionForUser5;

	class CanaryForUser5 implements CanaryGateway {

		private $authStorage;

		public function __construct (AuthStorage $authStorage) {

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