<?php
	namespace Tilwa\IO\Mailing;

	use Tilwa\Contracts\{Services\Decorators\OnlyLoadedBy, Queues\Task};

	abstract class MailBuilder implements OnlyLoadedBy {

		protected $payload;

		final public function allowedConsumers ():array {

			return [Task::class];
		}

		public function setPayload ($data):self {

			$this->payload = $data;

			return $this;
		}

		abstract public function sendMessage ():void;
	}
?>