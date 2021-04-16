<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\Flows\Structures\AccessContext;

	use Tilwa\Contracts\CacheManager;

	class UpdateCountDelete {

		private $accessedContext;

		public function __construct (AccessContext $theAccessed) {

			$this->accessedContext = $theAccessed;
		}

		public function handle(CacheManager $cacheManager) {

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