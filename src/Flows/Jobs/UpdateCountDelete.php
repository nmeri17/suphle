<?php
	namespace Suphle\Flows\Jobs;

	use Suphle\Flows\{OuterFlowWrapper, UmbrellaSaver, Structures\AccessContext};

	use Suphle\Contracts\{IO\CacheManager, Queues\Task};

	/**
	 * This job runs after one of the possible renderers stored for a path has been accessed
	*/
	class UpdateCountDelete implements Task {

		public function __construct(

			protected readonly AccessContext $theAccessed,

			protected readonly UmbrellaSaver $flowSaver
		) {

			//
		}

		public function handle ():void {

			$accessed = $this->theAccessed;

			$routeUmbrella = $accessed->getRouteUmbrella();

			$accessingUser = $accessed->getUser();

			$mainFlow = $accessed->getRouteUserNode();

			$urlPattern = $accessed->getPath();

			$hits = $mainFlow->currentHits();

			if ($hits >= $mainFlow->getMaxHits( $accessingUser, $urlPattern )-1) // this task only runs when a flow has been accessed. If maxHits = 0, we don't want to access it on the next visit

				$routeUmbrella->clearUser($accessingUser);

			else {
				$mainFlow->incrementHits();

				$routeUmbrella->addUser($accessingUser, $mainFlow);
			}

			$this->flowSaver->updateUmbrella($urlPattern, $routeUmbrella);
		}
	}
?>