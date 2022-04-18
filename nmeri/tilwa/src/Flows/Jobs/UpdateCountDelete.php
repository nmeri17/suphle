<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Flows\Structures\AccessContext;

	use Tilwa\Contracts\{CacheManager, Queues\Task};

	/**
	 * This job runs after one of the possible renderers stored for a path has been accessed
	*/
	class UpdateCountDelete implements Task {

		private $accessedContext, $cacheManager;

		public function __construct (AccessContext $theAccessed, CacheManager $cacheManager) {

			$this->accessedContext = $theAccessed;

			$this->cacheManager = $cacheManager;
		}

		public function handle ():void {

			$accessed = $this->accessedContext;

			$routeUmbrella = $accessed->getRouteUmbrella();

			$accessingUser = $accessed->getUser();

			$mainFlow = $accessed->getRouteUserNode();

			$urlPattern = $accessed->getPath();

			$hits = $mainFlow->currentHits();

			if ($hits >= $mainFlow->getMaxHits( $accessingUser, $urlPattern ))

				$routeUmbrella->clearUser($accessingUser);

			else {
				$mainFlow->incrementHits();

				$routeUmbrella->addUser($accessingUser, $mainFlow);
			}
			
			$this->cacheManager->save($urlPattern, $routeUmbrella); // override whatever was there
		}
	}
?>