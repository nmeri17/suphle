<?php
	namespace Suphle\Contracts\Queues;

	interface Task {

		public function handle ():void;
	}
?>