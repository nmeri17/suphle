<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	use Tilwa\Contracts\Auth\AuthStorage;

	class AttemptsLoadingUser extends BaseMiddleware {

		private $authStorage;

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function process ($request, $requestHandler) {

			return $requestHandler->handle($request);
		}
	}
?>