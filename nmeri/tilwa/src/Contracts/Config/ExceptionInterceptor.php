<?php
	namespace Tilwa\Contracts\Config;

	interface ExceptionInterceptor extends ConfigMarker {

		public function errorHandlers ():array;
	}
?>