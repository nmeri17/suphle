<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Request\RouteRule;

	class ModelEditRule extends RouteRule {

		private $modelService;

		public function __construct (AuthStorage $authStorage, $modelService) {

			$this->modelService = $modelService;

			parent::__construct($authStorage);
		}

		public function permit ():bool {

			$user = $this->authStorage->getUser();

			return $user->isAdmin() || $user->getId() == $this->modelService->getCreatorId();
		}
	}
?>