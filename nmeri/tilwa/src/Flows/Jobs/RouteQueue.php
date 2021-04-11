<?php

	namespace Tilwa\Flows;

	use Tilwa\Events\EventManager;

	class RouteQueueJob implements Job {

		private $context;

		public function __construct (FlowContext $outgoingContext) {

			$this->context = $outgoingContext;
		}

		public function handle(FlowHydrator $hydrator, EventManager $eventManager) {

			$this->context->setEventManager($eventManager);

			$hydrator->setContext($this->context)

			->runNodes()->store();
		}
	}
?>