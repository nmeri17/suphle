<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Bootstrap;

	use Tilwa\Contracts\CanaryGateway;

	class CanaryValidator {

		private $module;

		function __construct(Bootstrap $module) {
			
			$this->module = $module;
		}

		public function validate (array $canaries):array {

			return array_filter($canaries, function ($canary) {

				$instance = $this->module->getClass($canary);
				
				return $instance instanceof CanaryGateway && $this->module->getClass($instance->entryClass()) instanceof RouteCollection;
			});
			
			
		}
	}
?>