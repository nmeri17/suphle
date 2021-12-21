<?php
	namespace Tilwa\Routing;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{CanaryGateway, RouteCollection};

	class CanaryValidator {

		private $container;

		public function __construct(Container $container) {
			
			$this->container = $container;
		}

		// @return array of passing canaries
		public function validate (array $canaries):array {

			return array_filter($canaries, function ($canary) {

				$instance = $this->container->getClass($canary);
				
				return is_subclass_of($instance, CanaryGateway::class) &&
				is_subclass_of($instance->entryClass(), RouteCollection::class);
			});
		}
	}
?>