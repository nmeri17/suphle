<?php
	namespace Suphle\Routing\Structures;

	class PlaceholderCheck {

		public function __construct(private readonly string $routeState, private readonly string $methodName)
  {
  }

		public function getMethodName ():string {

			return $this->methodName;
		}

		public function getRouteState ():string {

			return $this->routeState;
		}
	}
?>