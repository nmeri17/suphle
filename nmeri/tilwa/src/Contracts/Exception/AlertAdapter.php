<?php
	namespace Tilwa\Contracts\Exception;

	use Tilwa\Request\PayloadStorage;

	use Throwable;

	interface AlertAdapter {

		public function broadcastException (Throwable $exception, PayloadStorage $payloadStorage);
	}
?>