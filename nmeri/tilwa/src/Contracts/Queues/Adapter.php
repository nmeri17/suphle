<?php
	namespace Tilwa\Contracts\Queues;

	interface Adapter {

		public function pushAction (string $taskClass, array $payload):void;

		public function processTasks ():void;

		public function configureNative ():void;
	}
?>