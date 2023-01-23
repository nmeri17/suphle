<?php
	namespace Suphle\Contracts\Presentation;

	use Suphle\Flows\ControllerFlows;

	use Suphle\Services\ServiceCoordinator;

	interface BaseRenderer {

		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function setCoordinatorClass (ServiceCoordinator $class):void;

		public function getCoordinator ():ServiceCoordinator;

		public function matchesHandler (string $name):bool;

		public function setHeaders (int $statusCode, array $headers):void;

		public function setRawResponse($response):self;

		public function setFlow (ControllerFlows $flow):self;

		public function getFlow ():?ControllerFlows;

		public function getRawResponse():iterable;

		public function getRouteMethod ():string;

		public function setRouteMethod (string $httpMethod):void;

		public function getStatusCode ():int;

		public function getHeaders ():array;

		public function isSerializable ():bool;
	}
?>