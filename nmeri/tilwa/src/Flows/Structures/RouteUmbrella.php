<?php
	namespace Tilwa\Flows\Structures;

	use DateTime;

	// this is the object value for each route key in the cache i.e. cache = ["path-x" => RouteUmbrella]
	class RouteUmbrella {

		private $users;

		private $routeName;

		function __construct(string $routeName) {
			
			$this->routeName = $routeName;
		}

		public function addUser(string $userId, FlowContext $flowContext):void {

			$this->users[$userId] = $flowContext;
		}

		public function getUserPayload(string $userId):FlowContext {

			$context = $this->users[$userId];

			if (!$context || $context->getExpiresAt() >= new DateTime) return;

			return $context;
		}

		public function clearUser(string $userId):void {

			unset($this->users[$userId]);
		}
	}
?>