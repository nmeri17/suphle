<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\Events\EventManager;

	use Tilwa\Flows\Structures\FlowContext;

	class RouteQueue {

		private $context;

		public function __construct (FlowContext $outgoingContext) {

			$this->context = $outgoingContext;
		}

		public function handle(FlowHydrator $hydrator, EventManager $eventManager) {

			$this->context->setEventManager($eventManager);

			$hydrator->setContext($this->context)->runNodes();
		}
	}
?>