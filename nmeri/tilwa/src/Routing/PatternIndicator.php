<?php
	namespace Tilwa\Routing;

	use Tilwa\Contracts\Routing\RouteCollection;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Middleware\MiddlewareRegistry;

	class PatternIndicator {

		private $patternAuthentication, $registry, $authorizer;

		public function __construct (MiddlewareRegistry $registry, PathAuthorizer $authorizer) {

			$this->registry = $registry;

			$this->authorizer = $authorizer;
		}

		public function indicate (RouteCollection $collection, string $pattern):void {

			$this->setPatternAuthentication($collection, $pattern);

			$this->includeMiddleware($collection, $pattern);

			$this->updatePermissions($collection, $pattern);
		}

		// if a higher level security was applied to a child collection with its own rules, omitting the current pattern, the security will be withdrawn from that pattern
		public function setPatternAuthentication(RouteCollection $collection, string $pattern):void {

			$activePatterns = $collection->_authenticatedPaths();

			if (!empty($activePatterns)) {

				if (in_array($pattern, $activePatterns)) // case-sensitive comparison

					$this->patternAuthentication = $collection->_getAuthenticator();

				else $this->patternAuthentication = null;
			}
		}

		public function activeAuthStorage ():?AuthStorage {

			return $this->patternAuthentication;
		}

		public function getAuthorizer ():PathAuthorizer {

			return $this->authorizer;
		}

		public function includeMiddleware (RouteCollection $collection, string $segment):void {

			$collection->_assignMiddleware();

			$this->registry->updateStack($segment);
		}

		public function updatePermissions (RouteCollection $collection, string $pattern):void {

			$collection->_authorizePaths();

			$this->authorizer->updateRuleStatus($pattern);
		}
	}
?>