<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Flows\Structures\AccessContext;

	use Tilwa\Contracts\CacheManager;

	/**
	 * This job runs after one of the possible renderers stored for a path has been accessed
	*/
	class UpdateCountDelete {

		private $accessedContext, $cacheManager;

		public function __construct (AccessContext $theAccessed, CacheManager $cacheManager) {

			$this->accessedContext = $theAccessed;

			$this->cacheManager = $cacheManager;
		}

		public function handle() {

			$accessed = $this->accessedContext;

			$routeUmbrella = $accessed->getRouteUmbrella();

			$accessingUser = $accessed->getUser();

			$mainFlow = $accessed->getRouteUserNode();

			$hits = $mainFlow->currentHits();

			if ($hits >= $mainFlow->getMaxHits())

				$routeUmbrella->clearUser($accessingUser);

			else {
				$mainFlow->incrementHits();

				$routeUmbrella->addUser($accessingUser, $mainFlow);
			}
			
			$cacheManager->save($accessed->getPath(), $routeUmbrella); // override whatever was there
		}
	}
?>