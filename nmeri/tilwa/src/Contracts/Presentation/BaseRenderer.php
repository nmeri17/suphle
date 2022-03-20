<?php
	namespace Tilwa\Contracts\Presentation;

	use Tilwa\Hydration\Container;

	use Tilwa\Flows\ControllerFlows;

	interface BaseRenderer {

		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function getController():string;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function setControllingClass (string $class):void;

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