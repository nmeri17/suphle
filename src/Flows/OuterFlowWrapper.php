<?php
	namespace Suphle\Flows;

	use Suphle\Flows\Jobs\{RouteBranches, UpdateCountDelete};

	use Suphle\Flows\Structures\{RouteUserNode, RouteUmbrella,AccessContext, PendingFlowDetails};

	use Suphle\Contracts\{Requests\BaseResponseManager, IO\CacheManager, Auth\AuthStorage, Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Suphle\Modules\ModulesBooter;

	use Suphle\Queues\AdapterManager;

	use Suphle\Events\EventManager;

	use Suphle\Request\RequestDetails;

	class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler {

		final const ALL_USERS = "*", HIT_EVENT = "flow_hit";

		private $requestDetails, $queueManager, $modules,

		$flowSaver, $authStorage, $routeUmbrella,

		$activeUser, $eventManager, $routeUserNode;

		public function __construct(
			RequestDetails $requestDetails, AdapterManager $queueManager,
			UmbrellaSaver $flowSaver, AuthStorage $authStorage,

			EventManager $eventManager, ModulesBooter $modulesBooter
		) {
			
			$this->requestDetails = $requestDetails;

			$this->queueManager = $queueManager;

			$this->flowSaver = $flowSaver;

			$this->authStorage = $authStorage;

			$this->eventManager = $eventManager;

			$this->modules = $modulesBooter->getModules();
		}

		public function canHandle ():bool {

			$this->routeUmbrella = $this->flowSaver->getExistingUmbrella($this->dataPath());

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

			return $this->flowSaver->getPatternLocation(

				$this->requestDetails->getPath()
			);
		}

		private function emitEvents($cachedResponse):void {

			$renderer = $this->responseRenderer();

			$this->eventManager->emit(

				$renderer->getController()::class,

				$renderer->getHandler(),

				$cachedResponse // event handler can then inject payloadStorage/pathPlaceholders
			);
		}
 
		private function queueBranches():void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				"context" => new PendingFlowDetails(
					$this->responseRenderer(),

					$this->authStorage->getUser()
				)
			]);
		}

		public function responseRenderer ():BaseRenderer {

			return $this->routeUserNode->getRenderer();
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->responseRenderer();
		}
	}
?>