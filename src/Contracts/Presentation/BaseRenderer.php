<?php
	namespace Suphle\Contracts\Presentation;

	use Suphle\Flows\ControllerFlows;

	use Suphle\Services\ServiceCoordinator;

	/**
	 * Psr\Http\Message\ResponseInterface, if you will
	*/
	interface BaseRenderer {

		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function setCoordinatorClass (ServiceCoordinator $class):void;

		public function getCoordinator ():ServiceCoordinator;

		public function matchesHandler (string $name):bool;

		public function setHeaders (int $statusCode, array $headers):void;

		public function setRawResponse (iterable $response):self;

		public function setFlow (ControllerFlows $flow):self;

		public function getFlow ():?ControllerFlows;

		public function getRawResponse():iterable;

		public function getRouteMethod ():string;

		public function setRouteMethod (string $httpMethod):void;

		public function getStatusCode ():int;

		public function getHeaders ():array;

		/**
		 * Determines whether this renderer is fit for writing validation errors to directly or whether it should be deferred to the renderer of the preceding request
		*/
		public function deferValidationContent ():bool;

		public function forceArrayShape (array $includeData = []):void;
	}
?>