<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	class AttemptsLoadingUser extends BaseMiddleware {

		private $authStorage;

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function process (PayloadStorage $request, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return $requestHandler->handle($request);
		}
	}
?>