<?php

	namespace Suphle\Flows\Structures;

	class AccessContext {

		function __construct(private readonly string $path, private readonly RouteUserNode $unitPayload, private readonly RouteUmbrella $umbrella, private readonly string $userId)
  {
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