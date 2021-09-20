<?php
	namespace Tilwa\Middleware;

	use Psr\Http\Server\RequestHandlerInterface;

	use Tilwa\Contracts\Middleware;

	use Tilwa\Routing\RequestDetails;

	// wraps the actual middleware in a way that causes it to fire its successor
	class MiddlewareNexts implements RequestHandlerInterface {

		private $currentMiddleware, $nextHandler; 

		public function __construct (Middleware $currentMiddleware, ?self $nextHandler) {

			$this->nextHandler = $nextHandler;

			$this->currentMiddleware = $currentMiddleware
		}

		public function handle (RequestDetails $requestDetails) {

			return $this->currentMiddleware->process($requestDetails, $this->nextHandler);
		}
	}
?>