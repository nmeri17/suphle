<?php
	namespace Suphle\Adapters\Queues;

	use Suphle\Contracts\Queues\Adapter;

	abstract class BaseQueueAdapter implements Adapter {

		protected $activeQueueName, $client;

		public function setActiveQueue (string $queueName):void {

			$this->activeQueueName = $queueName;
		}

		public function getNativeClient () {

			return $this->client;
		}
	}
?>