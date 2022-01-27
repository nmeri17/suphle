<?php
	namespace Tilwa\Contracts\Exception;

	use Throwable;

	interface AlertAdapter {

		public function broadcastException (Throwable $exception);
	}
?>