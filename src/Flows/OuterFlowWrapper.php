<?php
	namespace Suphle\Flows;

	use Suphle\Flows\Jobs\{RouteBranches, UpdateCountDelete};

	use Suphle\Flows\Structures\{RouteUserNode, AccessContext, PendingFlowDetails};

	use Suphle\Contracts\{Requests\BaseResponseManager, IO\CacheManager, Auth\AuthStorage, Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Suphle\Modules\ModulesBooter;

	use Suphle\Queues\AdapterManager;

	use Suphle\Events\EventManager;

	use Suphle\Request\RequestDetails;

	use Suphle\Hydration\Container;

	class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler {

		final public const ALL_USERS = "*";

		private $modules, $routeUmbrella, $activeUser, $routeUserNode,

		$authStorage;

		public function __construct(
			private readonly RequestDetails $requestDetails,

			private readonly AdapterManager $queueManager,
			
			private readonly UmbrellaSaver $flowSaver,

			private readonly Container $container,

			private readonly EventManager $eventManager,

			ModulesBooter $modulesBooter
		) {
			
			$this->modules = $modulesBooter->getModules();
		}

		public function canHandle ():bool {

			$this->routeUmbrella = $this->flowSaver->getExistingUmbrella($this->dataPath());

			if (is_null($this->routeUmbrella)) return false;

			$this->setAuthFromStored();

			$this->routeUserNode = $this->getVisitorsFlow($this->getVisitingUserId() );

			$userHasContent = !is_null($this->routeUserNode);

			if (!$userHasContent)

				$this->container->refreshClass(AuthStorage::class);

			return $userHasContent;
		}

		protected function setAuthFromStored ():void {

			$this->authStorage = $this->container->getClass(

				$this->routeUmbrella->getAuthStorage()
			);

			$this->container->whenTypeAny()->needsAny([

				AuthStorage::class => $this->authStorage
			]);
		}

		private function getVisitorsFlow (string $visitorId):?RouteUserNode {

			$userPayload = $this->routeUmbrella->getUserPayload($visitorId);

			if (is_null($userPayload) && ($visitorId != self::ALL_USERS)) { // assume data was saved for general user base
				
				$visitorId = self::ALL_USERS;

				$userPayload = $this->routeUmbrella->getUserPayload($visitorId);
			}

			$this->activeUser = $visitorId;

			return $userPayload;
		}

		private function getVisitingUserId ():string {

			$user = $this->authStorage->getUser();

			return is_null($user) ? self::ALL_USERS: strval($user->getId());
		}

		public function afterRender($cachedResponse):void {

			$this->emitEvents($cachedResponse);

			$this->queueBranches();
		}
		
		public function emptyFlow():void {

			$this->queueManager->addTask(UpdateCountDelete::class, [
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

			$this->queueManager->addTask(RouteBranches::class, [
				
				"context" => new PendingFlowDetails(
					
					$this->responseRenderer(), $this->authStorage
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