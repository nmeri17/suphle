<?php
	namespace Suphle\Middleware;

	use Suphle\Contracts\Routing\Middleware;

	use Suphle\Request\PayloadStorage;

	/**
	 * Wraps the actual middleware in a way that causes it to fire its successor
	*/
	class MiddlewareNexts {

		public function __construct(private readonly Middleware $currentMiddleware, private readonly ?\Suphle\Middleware\MiddlewareNexts $nextHandler)
  {
  }

		public function handle (PayloadStorage $payloadStorage) {

			return $this->currentMiddleware->process($payloadStorage, $this->nextHandler);
		}
	}
?>