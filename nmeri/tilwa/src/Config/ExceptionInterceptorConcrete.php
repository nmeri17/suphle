<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\ExceptionInterceptor;

	class ExceptionInterceptorConcrete implements ExceptionInterceptor {

		public function errorHandlers ():array {

			return [
				NotFoundException::class => "handler",

				Unauthenticated::class => "handler",

				ValidationFailure::class => "handler",

				IncompatibleHttpMethod::class => "handler"
			];
		}
	}
?>