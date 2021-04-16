<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\CacheManager;

	use Tilwa\Flows\Structures\{FlowContext, RouteUmbrella};

	use Tilwa\Flows\Previous\{ SingleNode, CollectionNode};

	class FlowHydrator {

		const ALL_USERS = "*";

		private $context;

		private $computedNodes;

		private $cacheManager;

		private $authenticator;

		private $userId;

		function __construct(CacheManager $cacheManager, Authenticator $authenticator, ) {

			$this->cacheManager = $cacheManager;

			$this->authenticator = $authenticator;
		}

		public function setContext(FlowContext $context):self {

			$this->context = $context;
		}

		# @param {contentType} model type, where present
		private function storeContext(string $urlPattern, string $contentType):void {

			$manager = $this->cacheManager;
			
			$umbrella = $manager->get($urlPattern);

			if (!$umbrella) $umbrella = new RouteUmbrella($urlPattern);

			$umbrella->addUser($this->getUserId(), $this->context); // it's supposed to be different different contexts per umbrella/user

			$saved = $manager->save($urlPattern, $umbrella);

			if ($contentType) $saved->tag($contentType);

			// better still, this guy can subscribe to a topic(instead of using tags?). update listener publishes to that topic (so we hopefully have no loop)
		}

		// call the appropriate triggers depending on action specified on it
		public function runNodes():self {

			$branchTypeHandlers = [
				SingleNode::class => "handleSingleNodes",

				CollectionNode::class => "handleCollectionNodes"
			];

			foreach ($this->context->getBranches() as $urlPattern => $branch) { // this is on the controller flow, not this object
				$handler = $branchTypeHandlers[$branch::class];

				$builtNode = $this->$handler($branch);

				$contentType = $this->getContentType($builtNode);
				$node = $this->getUserNode($urlPattern);

				$this->storeContext($urlPattern, $contentType)
			};

			// work with the controller flow expiry time and co
			return $this;
		}

		private function getUserId():string {

			$userId = $this->userId;

			if (!is_null($userId)) return $userId;

			$user = $this->authenticator->getUser();

			$userId = !$user ? self::ALL_USERS: strval($user->id);

			return $this->userId = $userId;
		}

		public function getUserNode(string $pattern):RouteUserNode {
			// find and set this pattern's renderer. then create a RouteUserNode out of that
			// there's simply no way of conveying those modules to this guy
			// is richer in wealth and knowledge than the deep sitted self-esteem
		}
	}
?>