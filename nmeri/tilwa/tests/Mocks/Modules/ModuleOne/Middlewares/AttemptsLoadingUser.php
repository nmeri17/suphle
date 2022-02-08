<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	class AttemptsLoadingUser extends BaseMiddleware {

		private $user;

		public function __construct (User $user) {

			$this->user = $user;
		}

		public function process ($request, $requestHandler) {

			return $requestHandler->handle($request);
		}
	}
?>