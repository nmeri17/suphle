<?php
	namespace Suphle\Contracts\Exception;

	use Throwable;

	interface AlertAdapter {

		// note: exception has a `getTrace()`
		public function broadcastException (Throwable $exception, $activePayload):void;
	}
?>