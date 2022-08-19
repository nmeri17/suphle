<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Request\RouteRule;

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