<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\{Requests\BaseResponseManager, CacheManager, Auth\AuthStorage, App\HighLevelRequestHandler};

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Flows\Jobs\{RouteBranches, BranchesContext, UpdateCountDelete};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella,AccessContext};

	use Tilwa\Events\EventManager;

	use Tilwa\Routing\RequestDetails;

	class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler {

		const FLOW_PREFIX = "tilwa_flow",

		ALL_USERS = "*", HIT_EVENT = "flow_hit";

		private $requestDetails, $queueManager, $modules,

		$cacheManager, $authStorage, $routeUmbrella,

		$activeUser, $eventManager;

		public function __construct(RequestDetails $requestDetails, AdapterManager $queueManager, array $modules, CacheManager $cacheManager, AuthStorage $authStorage, EventManager $eventManager) {
			
			$this->requestDetails = $requestDetails;

			$this->queueManager = $queueManager;

			$this->modules = $modules;

			$this->cacheManager = $cacheManager;

			$this->authStorage = $authStorage;

			$this->eventManager = $eventManager;
		}

		private function getUserId():string { 

			$user = $this->authStorage->getUser();

			return is_null($user) ? self::ALL_USERS: strval($user->getId());
		}

		public function canHandle():bool {

			$this->routeUmbrella = $this->cacheManager->get($this->dataPath()); // or combine [tag] with the [get]

			if (is_null($this->routeUmbrella)) return false;

			$this->context = $this->getActiveFlow($this->getUserId() );

			return !is_null($this->context);
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

			$this->queueManager->augmentArguments(UpdateCountDelete::class, [
				new AccessContext(

					$this->dataPath(), $this->context,

					$this->routeUmbrella, $this->activeUser
				)
			]);
		}

		private function dataPath ():string {

			return self::FLOW_PREFIX . $this->requestDetails->getPath();
		}

		// it is safest for listeners to listen "external" on the target controller
		private function emitEvents($cachedResponse):void {

			$this->eventManager->emit(

				$this->handlingRenderer()->getController(), self::HIT_EVENT, $cachedResponse
			); // should probably include incoming request parameters?
		}
 
		private function queueBranches():void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				new BranchesContext(
					$this->handlingRenderer(),

					$this->authStorage->getUser(),

					$this->modules
				)
			]);
		}

		public function handlingRenderer ():AbstractRenderer {

			return $this->context->getRenderer();
		}
	}
?>