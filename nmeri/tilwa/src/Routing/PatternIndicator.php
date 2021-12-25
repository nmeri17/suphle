<?php
	namespace Tilwa\Routing;

	use Tilwa\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Middleware\MiddlewareRegistry;

	class PatternIndicator {

		private $patternAuthentication, $registry, $authorizer,

		$defaultAuthenticator;

		public function __construct (MiddlewareRegistry $registry, PathAuthorizer $authorizer) {

			$this->registry = $registry;

			$this->authorizer = $authorizer;
		}

		public function indicate (RouteCollection $collection, string $pattern):void {

			$this->setPatternAuthentication($collection, $pattern);

			$this->includeMiddleware($collection, $pattern);

			$this->updatePermissions($collection, $pattern);
		}

		public function setDefaultAuthenticator (AuthStorage $mechanism):void {

			$this->defaultAuthenticator = $mechanism;
		}

		/**
		 *  If a higher level security was applied to a child collection with its own rules, omitting the current pattern, the security will be withdrawn from that pattern
		*/
		public function setPatternAuthentication(RouteCollection $collection, string $pattern):void {

			$activePatterns = $collection->_authenticatedPaths();

			if (!empty($activePatterns)) {

				if (in_array($pattern, $activePatterns)) // case-sensitive comparison

					$this->patternAuthentication = $this->defaultAuthenticator ?? $collection->_getAuthenticator();

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

		/**
		 * Useful in settings where a module has more than one route collection. The preceding one could have updated lists that would undesirably affect the oncoming collection
		*/
		public function resetIndications ():void {

			$this->patternAuthentication = null;

			$this->registry->emptyAllStacks();

			$this->authorizer->forgetAllRules();
		}
	}
?>