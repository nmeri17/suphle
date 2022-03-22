<?php
	namespace Tilwa\Contracts\Bridge;

	interface LaravelArtisan {

		public function call ($command, array $parameters = [], $outputBuffer = null):int
	}
?>