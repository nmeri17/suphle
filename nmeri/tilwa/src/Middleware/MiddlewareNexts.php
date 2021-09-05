<?php
	namespace Tilwa\Middleware;

	use Psr\Http\Server\RequestHandlerInterface;

	use Tilwa\Contracts\Middleware;

	use Tilwa\Request\ValidatorManager;

	// wraps the actual middleware in a way that causes it to fire its successor
	class MiddlewareNexts implements RequestHandlerInterface {

		private $currentMiddleware, $nextHandler; 

		public function __construct (Middleware $currentMiddleware, MiddlewareNexts $nextHandler) {

			$this->nextHandler = $nextHandler;

			$this->currentMiddleware = $currentMiddleware
		}

		public function handle (ValidatorManager $validatorManager) {

			return $this->currentMiddleware->process($validatorManager, $this->nextHandler);
		}
	}
?>