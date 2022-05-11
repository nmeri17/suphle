<?php
	namespace Tilwa\Contracts\Presentation;

	use Tilwa\Hydration\Container;

	use Tilwa\Flows\ControllerFlows;

	use Tilwa\Services\ServiceCoordinator;

	interface BaseRenderer {

		/**
		 * Assumes [hydrateDependencies] has been called earlier
		*/
		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function setControllingClass (ServiceCoordinator $class):void;

		public function getController ():ServiceCoordinator;

		public function hydrateDependencies( Container $container):void;

		public function getDependencies ():array;

		public function matchesHandler (string $name):bool;

		public function setHeaders (int $statusCode, array $headers):void;

		public function setRawResponse($response):self;

		public function setFlow (ControllerFlows $flow):self;

		public function getFlow ():ControllerFlows;

		public function getRawResponse();

		public function getPath():string;

		public function setPath (string $path):void;

		public function getRouteMethod ():string;

		public function setRouteMethod (string $httpMethod):void;

		public function getStatusCode ():int;

		public function getHeaders ():array;
	}
?>