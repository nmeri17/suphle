<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	use Suphle\Request\PathAuthorizer;

	use Suphle\Middleware\MiddlewareRegistry;

	class PatternIndicator {

		private $patternAuthentication;
  private $providedAuthenticator;

		public function __construct (

			protected readonly MiddlewareRegistry $registry, 

			protected readonly PathAuthorizer $authorizer
		) {

			//
		}

		public function indicate (RouteCollection $collection, string $pattern):void {

			$this->includeMiddleware($collection, $pattern);

			// $this->updatePermissions($collection, $pattern); // should remove
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