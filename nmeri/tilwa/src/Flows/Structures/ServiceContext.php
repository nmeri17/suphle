<?php

	namespace Tilwa\Flows\Structures;

	class ServiceContext {

		/**
		* @property {serviceName} where we'll be pulling the data we intend to filter into another operation
		*/
		private $serviceName, $method;

		function __construct(string $serviceName, string $method) {

			$this->serviceName = $serviceName;

			$this->method = $method;
		}

		public function getServiceName():string {
			
			return $this->serviceName;
		}

		public function getMethod():string {
			
			return $this->method;
		}
	}
?>