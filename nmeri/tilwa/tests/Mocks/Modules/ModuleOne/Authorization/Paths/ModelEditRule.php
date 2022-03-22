<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Tilwa\Request\RouteRule;

	class ModelEditRule extends RouteRule {

		protected $authorizedUser;

		private $modelService;

		public function __construct (AuthStorage $authStorage, $modelService) {

			$this->authorizedUser = $authStorage->getUser();

			$this->modelService = $modelService;
		}

		public function permit ():bool {

			$user = $this->authorizedUser;

			return $user->isAdmin() || $user->getId() == $modelService->getCreatorId();
		}
	}
?>