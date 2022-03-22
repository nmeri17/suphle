<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	class AlterFinalResponse extends BaseMiddleware {

		public function process ($payloadStorage, $requestHandler) {

			$decodedResponse = json_decode($requestHandler->handle($payloadStorage), true);

			return json_encode( array_merge($decodedResponse, ["foo" => "baz"]));
		}
	}
?>