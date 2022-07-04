<?php
	namespace Tilwa\Contracts\Presentation;

	use Tilwa\Flows\ControllerFlows;

	use Tilwa\Services\ServiceCoordinator;

	interface BaseRenderer {

		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function setControllingClass (ServiceCoordinator $class):void;

		public function getController ():ServiceCoordinator;

		public function matchesHandler (string $name):bool;

		public function setHeaders (int $statusCode, array $headers):void;

		public function setRawResponse($response):self;

		public function setFlow (ControllerFlows $flow):self;

		public function getFlow ():?ControllerFlows;

		public function getRawResponse();

		public function getRouteMethod ():string;

		public function setRouteMethod (string $httpMethod):void;

		public function getStatusCode ():int;

		public function getHeaders ():array;

		public function dependencyMethods ():array; // even though this is an implementation detail unique to GenericRenderer, renderer callers manually pass the renderer to the decorator, who expects this method to be present
	}
?>