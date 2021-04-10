<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{ResponseManager as ManagerInterface, QueueManager};

	use Tilwa\Events\EventManager;

	class OuterFlowWrapper implements ManagerInterface {

		private $incomingPattern;

		private $queueManager;

		private $modules;

		public function __construct(string $pattern, QueueManager $queueManager, array $modules) {
			
			$this->incomingPattern = $pattern;

			$this->queueManager = $queueManager;

			$this->modules = $modules;
		}

		public function matchesUrl():bool {
			
			// work with $this->incomingPattern
		}

		public function setContext($user) {

			$this->context = $this->getActiveFlow( $user);
		}

		public function getResponse():string {

			return $this->context->getRenderer()->setRawResponse(
				
				$this->context->getPayload()
			)->render();
		}

		private function getActiveFlow( $user):FlowContext {
			
			// find the flow request in the cache matching current get parameters
			// use [$this->incomingPattern]
		}

		public function afterRender($cachedResponse):void {

			$this->emitEvents($cachedResponse);

			$this->queueBranches();
		}
		
		public function discard():bool {
			# delete items under this tag(s). expire the caches in [ttl] mins but delete each route pattern after it's accessed
			// if ($context->getBranches()->getTTL(userId, pattern) > $whenSaved)
		}

		private function emitEvents($cachedResponse):void {

			$context = $this->context;

			$context->getEventManager()->emit(

				$context->getRenderer()->getController(), "on_flow_hit", $cachedResponse
			); // should probably include incoming request parameters?
		}
 
		private function queueBranches():void {

			$this->queueManager->push(RouteBranchesJob::class, 

				new BranchesContext($this->incomingPattern, $this->modules )
			);
		}
	}
?>