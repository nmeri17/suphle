<?php

	namespace Suphle\Flows\Structures;

	class AccessContext {

		private $path, $unitPayload, $umbrella, $userId;

		function __construct(string $path, RouteUserNode $unitPayload, RouteUmbrella $umbrella, string $userId) {

			$this->path = $path;

			$this->unitPayload = $unitPayload;

			$this->umbrella = $umbrella;

			$this->userId = $userId;
		}

		public function getRouteUmbrella():RouteUmbrella {

			return $this->umbrella;
		}

		public function getPath():string {

			return $this->path;
		}

		public function getUser():string {

			return $this->userId;
		}

		public function getRouteUserNode():RouteUserNode {

			return $this->unitPayload;
		}
	}
?>