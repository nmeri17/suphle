<?php
	namespace Tilwa\IO\Mailing;

	use Tilwa\Contracts\{Services\Decorators\OnlyLoadedBy, Queues\Task};

	abstract class MailBuilder implements OnlyLoadedBy {

		protected $payload;

		final public function allowedConsumers ():array {

			return [Task::class];
		}

		public function setPayload ($data):static {

			$this->payload = $data;
		}

		abstract public function sendMessage ():void;
	}
?>