<?php
	namespace Suphle\Contracts\Config;

	interface ExceptionInterceptor extends ConfigMarker {

		public function getHandlers ():array;

		public function defaultHandler ():string;

		public function shutdownLog ():string;

		/**
		 * The last thing user should see after disgracefulShutdown details have been logged
		*/
		public function shutdownText ():string;
	}
?>