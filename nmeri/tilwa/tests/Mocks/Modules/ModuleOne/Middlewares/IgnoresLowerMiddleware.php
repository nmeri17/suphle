<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	class IgnoresLowerMiddleware extends BaseMiddleware {

		public function process ($payloadStorage, $requestHandler) {

			return ["foo" => "bar"];
		}
	}
?>