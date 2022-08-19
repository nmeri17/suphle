<?php
	namespace Suphle\Routing\Structures;

	class PlaceholderCheck {

		private $routeState, $methodName;

		public function __construct (string $newRouteState, string $currentMethod ) {

			$this->routeState = $newRouteState;

			$this->methodName = $currentMethod;
		}

		public function getMethodName ():string {

			return $this->methodName;
		}

		public function getRouteState ():string {

			return $this->routeState;
		}
	}
?>