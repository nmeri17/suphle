<?php
	namespace Tilwa\IO\Mailing;

	use Tilwa\Contracts\{Services\OnlyLoadedBy, Queues\Task};

	class MailBuilder implements OnlyLoadedBy {

		final public function allowedConsumers ():array {

			return [Task::class];
		}
	}
?>