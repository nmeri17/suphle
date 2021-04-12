<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\CacheManager;

	use Tilwa\Flows\Structures\{FlowContext, RouteUmbrella};

	class FlowHydrator {

		private $context;

		private $computedNodes;

		private $cacheManager;

		function __construct(CacheManager $cacheManager) {
			
			$this->cacheManager = $cacheManager;
		}

		public function setContext(FlowContext $context):self {

			$this->context = $context;
		}

		# @param {contentType} model type, where present
		private function storeContext(string $urlPattern, string $userId, string $contentType):void {

			$manager = $this->cacheManager;
			
			$umbrella = $manager->get($urlPattern);

			if (!$umbrella) $umbrella = new RouteUmbrella($urlPattern);

			$umbrella->addUser($userId, $this->context);

			$saved = $manager->save($urlPattern, $umbrella);

			if ($contentType) $saved->tag($contentType);
		}

		// call the appropriate triggers depending on action specified on it
		public function runNodes():self {

			$this->context->getBranches(); // this is on the controller flow, not this object

			// use something like $branches = flow->branches->each(select action handler)
			// work with the controller flow expiry time and co

			// better still, this guy can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we hopefully have no loop)

			//$this->storeContext()
		}
	}
?>