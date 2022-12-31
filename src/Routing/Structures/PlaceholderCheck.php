<?php
	namespace Suphle\Routing\Structures;

	class PlaceholderCheck {

		public function __construct(protected readonly string $routeState, protected readonly string $methodName) {

			//
		}

		public function getMethodName ():string {

			return $this->methodName;
		}

		public function getRouteState ():string {

			return $this->routeState;
		}
	}
?>