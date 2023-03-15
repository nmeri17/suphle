<?php
	namespace Suphle\Auth\RequestScrutinizers;

	use Suphle\Hydration\Container;

	use Suphle\Routing\Structures\BaseScrutinizerHandler;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Exception\Explosives\Unauthenticated;

	class AuthenticateHandler extends BaseScrutinizerHandler {

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
		public function scrutinizeRequest ():void {

			$routedMechanism = end($this->metaFunnels)->authStorage;

			/*$switchedMechanism = $this->indicator->getProvidedAuthenticator(); // mirrored bits. should it be coupled to this handler or does it deserve its own

			if (!is_null($switchedMechanism))

				$routedMechanism = $switchedMechanism;*/

			if ( is_null($routedMechanism->getId()))

				throw new Unauthenticated($routedMechanism);

			$this->container->whenTypeAny()

			->needsAny([ AuthStorage::class => $routedMechanism]);
		}
	}
?>