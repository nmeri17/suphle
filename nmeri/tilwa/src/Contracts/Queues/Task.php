<?php
	namespace Tilwa\Contracts\Queues;

	interface Task {

		public function handle ():void;
	}
?>