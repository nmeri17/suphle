<?php

	namespace Suphle\Flows\Structures;

	class AccessContext {

		function __construct(protected readonly string $path, protected readonly RouteUserNode $unitPayload, protected readonly RouteUmbrella $umbrella, protected readonly string $userId) {

			//
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