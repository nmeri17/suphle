<?php
	namespace Suphle\Auth\Middleware;

	use Suphle\Request\PayloadStorage;

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Contracts\{Presentation\BaseRenderer, Auth\AuthStorage};

	use Suphle\Exception\Explosives\Unauthenticated;

	class AuthenticateHandler extends CollectibleMiddlewareHandler {

		public function __construct (

			protected readonly Container $container
		) {

			//
		}

		/**
		 * It'll override the default authStorage method provided
		 * 
		 * @throws Unauthenticated
		*/
		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$routedMechanism = end($this->collectors)->authStorage;

			/*$switchedMechanism = $this->indicator->getProvidedAuthenticator(); // mirrored bits

			if (!is_null($switchedMechanism))

				$routedMechanism = $switchedMechanism;*/

			if ( is_null($routedMechanism->getId()))

				throw new Unauthenticated($routedMechanism);

			$this->container->whenTypeAny()

			->needsAny([ AuthStorage::class => $routedMechanism]);

			return $requestHandler->handle($payloadStorage);
		}
	}
?>