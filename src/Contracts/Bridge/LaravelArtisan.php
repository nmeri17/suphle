<?php
	namespace Suphle\Contracts\Bridge;

	interface LaravelArtisan {

		public function invokeCommand ($command, array $parameters = []):int;
	}
?>