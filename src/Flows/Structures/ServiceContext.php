<?php
	namespace Suphle\Flows\Structures;

	class ServiceContext {

		function __construct(
      /**
       * @property {serviceName} where we'll be pulling the data we intend to filter into another operation
       */
      private readonly string $serviceName,
      /**
       * @property {serviceName} where we'll be pulling the data we intend to filter into another operation
       */
      private readonly string $method
  )
  {
  }

		public function getServiceName():string {
			
			return $this->serviceName;
		}

		public function getMethod():string {
			
			return $this->method;
		}
	}
?>