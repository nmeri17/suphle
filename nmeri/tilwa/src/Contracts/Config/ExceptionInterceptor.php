<?php
	namespace Tilwa\Contracts\Config;

	interface ExceptionInterceptor extends ConfigMarker {

		public function getHandlers ():array;

		public function defaultHandler ():string;

		public function shutdownLog ():string;

		public function shutdownText ():string;
	}
?>