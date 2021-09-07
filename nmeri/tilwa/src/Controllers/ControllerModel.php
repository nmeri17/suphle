<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\RequestValidator;

	/**
	 * Nothing can be done with this guy in the controller. Must be passed to a service
	*/
	abstract class ControllerModel {

		private $identifier;

		/**
		 * Use [identifier] to build the minimal state shared by all service methods for this model
		 * @return a query builder
		*/
		abstract public function getBuilder();

		public function setIdentifier(string $parameterValue):void {

			$this->identifier = $parameterValue;
		}
	}
?>