<?php

	namespace Tilwa\Flows\Structures;

	class AccessContext {

		private $path;

		private $flowContext;

		private $umbrella;

		private $userId;

		function __construct(string $path, FlowContext $flowContext, RouteUmbrella $umbrella, string $userId) {

			$this->path = $path;

			$this->flowContext = $flowContext;

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

		public function getFlowContext():FlowContext {

			return $this->flowContext;
		}
	}
?>