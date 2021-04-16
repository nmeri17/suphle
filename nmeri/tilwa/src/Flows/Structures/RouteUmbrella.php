<?php
	namespace Tilwa\Flows\Structures;

	use DateTime;

	// this is the object value for each route key in the cache i.e. cache = ["path-x" => RouteUmbrella]
	class RouteUmbrella {

		private $users;

		private $routeName;

		//private $nodeTags; // should give us a bird's eye view of the path to each model [collection] i.e. [Cows => "user35,foo", "user*,bar"]

		function __construct(string $routeName) {
			
			$this->routeName = $routeName;
		}

		public function addUser(string $userId, RouteUserNode $unitPayload):void {

			$this->users[$userId] = $unitPayload;
		}

		public function getUserPayload(string $userId):RouteUserNode {

			$context = $this->users[$userId];

			if (!$context || $context->getExpiresAt() >= new DateTime) return;

			return $context;
		}

		public function clearUser(string $userId):void {

			unset($this->users[$userId]);
		}
	}
?>