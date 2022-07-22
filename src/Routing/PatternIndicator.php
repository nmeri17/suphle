<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	use Suphle\Request\PathAuthorizer;

	use Suphle\Middleware\MiddlewareRegistry;

	class PatternIndicator {

		private $patternAuthentication, $registry, $authorizer,

		$providedAuthenticator;

		public function __construct (MiddlewareRegistry $registry, PathAuthorizer $authorizer) {

			$this->registry = $registry;

			$this->authorizer = $authorizer;
		}

		public function indicate (RouteCollection $collection, string $pattern):void {

			$this->setPatternAuthentication($collection, $pattern);

			$this->includeMiddleware($collection, $pattern);

			$this->updatePermissions($collection, $pattern);
		}

		/**
		 * We use this to switch authenticator during mirroring. But the binding can't happen here since it's too early to know what module will eventually handle request
		*/
		public function provideAuthenticator (AuthStorage $mechanism):void {

			$this->providedAuthenticator = $mechanism;
		}

		public function getProvidedAuthenticator ():?AuthStorage {

			return $this->providedAuthenticator;
		}

		/**
		 *  If a higher level security was applied to a child collection with its own rules, omitting the current pattern, the security will be withdrawn from that pattern
		*/
		public function setPatternAuthentication(RouteCollection $collection, string $pattern):void {

			$activePatterns = $collection->_authenticatedPaths();

			if (!empty($activePatterns)) {

				if (in_array($pattern, $activePatterns)) // case-sensitive comparison

					$this->patternAuthentication = $collection->_getAuthenticator();

				else $this->patternAuthentication = null;
			}
		}

		public function routedAuthStorage ():?AuthStorage {

			return $this->patternAuthentication;
		}

		public function getAuthorizer ():PathAuthorizer {

			return $this->authorizer;
		}

		public function includeMiddleware (RouteCollection $collection, string $segment):void {

			$collection->_assignMiddleware($this->registry);

			$this->registry->updateInteractedPatterns($segment);
		}

		public function updatePermissions (RouteCollection $collection, string $pattern):void {

			$collection->_authorizePaths($this->authorizer);

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