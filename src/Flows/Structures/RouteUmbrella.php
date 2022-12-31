<?php
	namespace Suphle\Flows\Structures;

	use Suphle\Hydration\Structures\ObjectDetails;

	use DateTime, Exception;

	/**
	 * This is the object value for each route key in the cache i.e. cache = ["path-x" => RouteUmbrella]
	 * 
	 * Using this instead of [user-x => [stored, resources]] since URL lookup/fail fast is faster than user hydration first
	 */
	class RouteUmbrella {

		protected array $users = [];

		protected string $authStorageName;

		//private $nodeTags; // should give us a bird's eye view of the path to each model [collection] i.e. [Cows => "user35,foo", "user*,bar"]

		public function __construct(
			protected readonly string $routeName,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function setAuthMechanism (string $storageName):void {

			if ($this->objectMeta->isInterface($storageName))

				throw new Exception("Storage mechanism must be a class", 500);

			$this->authStorageName = $storageName;
		}

		public function getAuthStorage ():string {

			return $this->authStorageName;
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