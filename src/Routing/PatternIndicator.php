<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\{Routing\RouteCollection, Auth\AuthStorage};

	use Suphle\Routing\PreMiddlewareRegistry;

	use Suphle\Middleware\MiddlewareRegistry;

	class PatternIndicator {

		private $patternAuthentication, $providedAuthenticator;

		public function __construct (

			protected readonly MiddlewareRegistry $middlewareRegistry, 

			protected readonly PreMiddlewareRegistry $preRegistry
		) {

			//
		}

		public function indicate (RouteCollection $collection, string $pattern):void {

			$this->includeMiddleware($collection, $pattern);

			$this->updateMeta($collection, $pattern);
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

		protected function includeMiddleware (RouteCollection $collection, string $segment):void {

			$collection->_assignMiddleware($this->middlewareRegistry);

			$this->middlewareRegistry->updateInteractedPatterns($segment);
		}

		protected function updateMeta (RouteCollection $collection, string $segment):void {

			$collection->_preMiddleware($this->preRegistry);

			$this->preRegistry->updateInteractedPatterns($segment);
		}

		/**
		 * Useful in settings where a module has more than one route collection. The preceding one could have updated lists that would undesirably affect the oncoming collection
		*/
		public function resetIndications ():void {

			$this->patternAuthentication = null;

			$this->middlewareRegistry->emptyAllStacks();

			$this->preRegistry->emptyAllStacks();
		}
	}
?>