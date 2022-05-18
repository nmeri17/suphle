<?php
	namespace Tilwa\Flows\Structures;

	use DateTime;

	// this is the object value for each route key in the cache i.e. cache = ["path-x" => RouteUmbrella]
	class RouteUmbrella {

		private $users = [], $routeName;

		//private $nodeTags; // should give us a bird's eye view of the path to each model [collection] i.e. [Cows => "user35,foo", "user*,bar"]

		public function __construct(string $routeName) {
			
			$this->routeName = $routeName;
		}

		public function addUser (string $userId, RouteUserNode $unitPayload):void {

			$this->users[$userId] = $unitPayload;
		}

		public function getUserPayload (string $userId):?RouteUserNode {

			if (!array_key_exists($userId, $this->users)) return null;

			$context = $this->users[$userId];

			$expiresAt = $context->getExpiresAt($userId, $this->routeName);

			if ($expiresAt >= new DateTime) return $context;

			return null;
		}

		public function clearUser(string $userId):void {

			unset($this->users[$userId]);
		}
	}
?>