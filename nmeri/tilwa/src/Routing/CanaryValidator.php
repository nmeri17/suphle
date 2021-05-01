<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\CanaryGateway;

	class CanaryValidator {

		private $container;

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		// @return array of passing canaries
		public function validate (array $canaries):array {

			return array_filter($canaries, function ($canary) {

				$instance = $this->container->getClass($canary);
				
				return $instance instanceof CanaryGateway && $this->container->getClass($instance->entryClass()) instanceof RouteCollection;
			});
			
			
		}
	}
?>