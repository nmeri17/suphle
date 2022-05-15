<?php
	namespace Tilwa\Flows;

	use Tilwa\Flows\Jobs\{RouteBranches, BranchesContext, UpdateCountDelete};

	use Tilwa\Flows\Structures\{RouteUserNode, RouteUmbrella,AccessContext};

	use Tilwa\Contracts\{Requests\BaseResponseManager, IO\CacheManager, Auth\AuthStorage, Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Tilwa\Modules\ModulesBooter;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Events\EventManager;

	use Tilwa\Request\RequestDetails;

	class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler {

		const FLOW_PREFIX = "tilwa_flow",

		ALL_USERS = "*", HIT_EVENT = "flow_hit";

		private $requestDetails, $queueManager, $modules,

		$cacheManager, $authStorage, $routeUmbrella,

		$activeUser, $eventManager, $routeUserNode;

		public function __construct(
			RequestDetails $requestDetails, AdapterManager $queueManager,
			CacheManager $cacheManager, AuthStorage $authStorage,

			EventManager $eventManager, ModulesBooter $modulesBooter
		) {
			
			$this->requestDetails = $requestDetails;

			$this->queueManager = $queueManager;

			$this->cacheManager = $cacheManager;

			$this->authStorage = $authStorage;

			$this->eventManager = $eventManager;

			$this->modules = $modulesBooter->getModules();
		}

		public function canHandle ():bool {

			$this->routeUmbrella = $this->cacheManager->getItem($this->dataPath()); // or combine [tag] with the [get]

			if (is_null($this->routeUmbrella)) return false;

			$this->routeUserNode = $this->getActiveFlow($this->getUserId() );

			return !is_null($this->routeUserNode);
		}

		private function getActiveFlow (string $userId):?RouteUserNode {

			$userPayload = $this->routeUmbrella->getUserPayload($userId);

			if (is_null($userPayload) && ($userId != self::ALL_USERS)) { // assume data was saved for general user base
				$userId = self::ALL_USERS;

				$userPayload = $this->routeUmbrella->getUserPayload($userId);
			}

			$this->activeUser = $userId;

			return $userPayload;
		}

		private function getUserId ():string { 

			$user = $this->authStorage->getUser();

			return is_null($user) ? self::ALL_USERS: strval($user->getId());
		}

		public function getResponse ():string {

			return $this->handlingRenderer()->render();
		}

		public function afterRender($cachedResponse):void {

			$this->emitEvents($cachedResponse);

			$this->queueBranches();
		}
		
		public function emptyFlow():void {

			$this->queueManager->augmentArguments(UpdateCountDelete::class, [
				"theAccessed" => new AccessContext(

					$this->dataPath(), $this->routeUserNode,

					$this->routeUmbrella, $this->activeUser
				)
			]);
		}

		private function dataPath ():string {

			return self::FLOW_PREFIX . "/" . trim($this->requestDetails->getPath(), "/");
		}

		// it is safest for listeners to listen "external" on the target controller
		private function emitEvents($cachedResponse):void {

			$controller = $this->handlingRenderer()->getController();

			$this->eventManager->emit(

				get_class($controller), self::HIT_EVENT, $cachedResponse
			); // should probably include incoming request parameters?
		}
 
		private function queueBranches():void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				"context" => new BranchesContext(
					$this->handlingRenderer(),

					$this->authStorage->getUser()
				)
			]);
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->routeUserNode->getRenderer();
		}
	}
?>