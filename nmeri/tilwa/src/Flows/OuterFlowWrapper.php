<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{Requests\BaseResponseManager, QueueManager, CacheManager, Auth\AuthStorage, App\HighLevelRequestHandler};

	use Tilwa\Flows\Jobs\{RouteBranches, BranchesContext, UpdateCountDelete};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella,AccessContext};

	use Tilwa\Events\EventManager;

	use Tilwa\Routing\RequestDetails;

	class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler {

		const FLOW_PREFIX = "tilwa_flow";

		const ALL_USERS = "*";

		private $requestDetails, $queueManager, $modules,

		$cacheManager, $authStorage, $routeUmbrella,

		$activeUser, $eventManager;

		public function __construct(RequestDetails $requestDetails, QueueManager $queueManager, array $modules, CacheManager $cacheManager, AuthStorage $authStorage, EventManager $eventManager) {
			
			$this->requestDetails = $requestDetails;

			$this->queueManager = $queueManager;

			$this->modules = $modules;

			$this->cacheManager = $cacheManager;

			$this->authStorage = $authStorage;

			$this->eventManager = $eventManager;
		}

		private function matchesUrl():bool {

			return !is_null($this->routeUmbrella);
		}

		private function setRouteUmbrella():void {

			$this->routeUmbrella = $this->cacheManager->get(self::FLOW_PREFIX . $this->requestDetails->getPath()); // or combine [tag] with the [get]
		}

		private function getUserId():string { 

			$user = $this->authStorage->getUser();

			return !$user ? self::ALL_USERS: strval($user->getId());
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

			return $this->handlingRenderer()->render();
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

			$path = self::FLOW_PREFIX . $this->requestDetails->getPath();

			$this->queueManager->push(UpdateCountDelete::class,
				new AccessContext($path, $this->context, $this->routeUmbrella, $this->activeUser )
			);
		}

		// it is safest for listeners to listen "external" on the target controller
		private function emitEvents($cachedResponse):void {

			$this->eventManager->emit(

				$this->handlingRenderer()->getController(), "on_flow_hit", $cachedResponse
			); // should probably include incoming request parameters?
		}
 
		private function queueBranches():void {

			$this->queueManager->push(RouteBranches::class, 

				new BranchesContext(
					$this->modules,

					$this->authStorage->getUser(),

					$this->handlingRenderer()
				)
			);
		}

		public function handlingRenderer ():AbstractRenderer {

			return $this->context->getRenderer();
		}
	}
?>