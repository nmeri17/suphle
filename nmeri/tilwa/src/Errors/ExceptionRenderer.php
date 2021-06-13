<?php

	namespace Tilwa\Errors;

	class ExceptionRenderer {

		private $handlers, $container;

		public function __construct(array $handlers, Container $container) {

			$this->handlers = $handlers;

			$this->container = $container;
		}

		public function registerHandlers ():void {

			set_exception_handler(function (Throwable $exception) {

				$exceptionName = get_class($exception);

				// wants to know the incoming path too
				if (array_key_exists($exceptionName, $this->handlers))

					echo $this->handlers[$exceptionName]->getResponse(); // can set response status codes with http_response_header inside these guys

				else throw $exception; // error formatter/whoops should wrap this
			});
		}
	}
?>