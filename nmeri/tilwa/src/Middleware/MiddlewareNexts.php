<?php
	namespace Tilwa\Middleware;

	use Psr\Http\Server\RequestHandlerInterface;

	// wraps the actual middleware in a way that causes it to fire its successor
	class MiddlewareNexts implements RequestHandlerInterface {

		private $currentMiddleware, $nextMiddleware; 

		public function __construct (Middleware $currentMiddleware, Middleware $nextMiddleware) {

			$this->nextMiddleware = $nextMiddleware;

			$this->currentMiddleware = $currentMiddleware
		}

		public function handle (BaseRequest $request) {

			return $this->currentMiddleware->process($request, new self($this->nextMiddleware));
		}
	}
?>