<?php
	namespace Tilwa\Middleware;

	use Tilwa\Contracts\Routing\Middleware;

	use Tilwa\Request\PayloadStorage;

	/**
	 * Wraps the actual middleware in a way that causes it to fire its successor
	*/
	class MiddlewareNexts {

		private $currentMiddleware, $nextHandler; 

		public function __construct (Middleware $currentMiddleware, ?self $nextHandler) {

			$this->nextHandler = $nextHandler;

			$this->currentMiddleware = $currentMiddleware;
		}

		public function handle (PayloadStorage $payloadStorage) {

			return $this->currentMiddleware->process($payloadStorage, $this->nextHandler);
		}
	}
?>