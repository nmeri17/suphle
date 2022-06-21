<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	class AltersPayloadStorage implements Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$payloadStorage->mergePayload($this->payloadUpdates());

			return $requestHandler->handle($payloadStorage);
		}

		public function payloadUpdates ():array {

			return ["foo" => "bar"];
		}
	}
?>