<?php

	interface QueueManager {

		public function push (string $job, $payload);

		public function addConnection (array $configuration);
	}
?>