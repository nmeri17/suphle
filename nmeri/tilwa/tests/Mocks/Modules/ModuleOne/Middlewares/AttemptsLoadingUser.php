<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	use Tilwa\Contracts\Auth\UserContract;

	class AttemptsLoadingUser extends BaseMiddleware {

		private $user;

		public function __construct (UserContract $user) {

			$this->user = $user;
		}

		public function process ($request, $requestHandler) {

			return $requestHandler->handle($request);
		}
	}
?>