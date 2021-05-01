<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{BaseResponseManager, QueueManager, CacheManager, Authenticator};

	use Tilwa\Flows\Jobs\{RouteBranches, BranchesContext, UpdateCountDelete};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella,AccessContext};

	use Tilwa\Events\EventManager;

	class OuterFlowWrapper implements BaseResponseManager {

		const FLOW_PREFIX = "tilwa_flow";

		const ALL_USERS = "*";

		private $incomingPattern;

		private $queueManager;

		private $modules;

		private $cacheManager;

		private $authenticator;

		private $routeUmbrella;

		private $activeUser;

		private $eventManager;

		public function __construct(string $pattern, QueueManager $queueManager, array $modules, CacheManager $cacheManager, Authenticator $authenticator, EventManager $eventManager) {
			
			$this->incomingPattern = $pattern;

			$this->queueManager = $queueManager;

			$this->modules = $modules;

			$this->cacheManager = $cacheManager;

			$this->authenticator = $authenticator;

			$this->eventManager = $eventManager;
		}

		private function matchesUrl():bool {

			return !is_null($this->routeUmbrella);
		}

		private function setRouteUmbrella():void {

			$this->routeUmbrella = $this->cacheManager->get(self::FLOW_PREFIX . $this->incomingPattern); // or combine [tag] with the [get]
		}

		private function getUserId():string { 

			$user = $this->authenticator->getUser();

			return !$user ? self::ALL_USERS: strval($user->id);
		}

		public function canHandle():bool {

			$this->setRouteUmbrella();

			if (!$this->matchesUrl()) return false;

			$this->setContext();

			return !is_null($this->context);
		}

		private function setContext():void {

			$userId = $this->getUserId();

			$this->context = $this->getActiveFlow( $userId);
		}

		public function getResponse():string {

			return $this->context->getRenderer()->setRawResponse(
				
				$this->context->getPayload()
			)->render();
		}

		private function getActiveFlow(string $userId):RouteUserNode {

			$context = $this->routeUmbrella->getUserPayload($userId);

			if (is_null($context) && ($userId != self::ALL_USERS)) { // assume data was saved for general user base
				$userId = self::ALL_USERS;

				$context = $this->routeUmbrella->getUserPayload($userId);
			}

			$this->activeUser = $userId;

			return $context;
		}

		public function afterRender($cachedResponse):void {

			$this->emitEvents($cachedResponse);

			$this->queueBranches();
		}
		
		public function emptyFlow():void {

			$path = self::FLOW_PREFIX . $this->incomingPattern;

			$this->queueManager->push(UpdateCountDelete::class,
				new AccessContext($path, $this->context, $this->routeUmbrella, $this->activeUser )
			);
		}

		// it is safest for listeners to listen "external" on the target controller
		private function emitEvents($cachedResponse):void {

			$this->eventManager->emit(

				$this->context->getRenderer()->getController(), "on_flow_hit", $cachedResponse
			); // should probably include incoming request parameters?
		}
 
		private function queueBranches():void {

			$user = $this->authenticator->getUser();

			$renderer = $this->context->getRenderer();

			$this->queueManager->push(RouteBranches::class, 

				new BranchesContext( $this->modules, $user, $renderer )
			);
		}
	}
?>