<?php
	namespace Suphle\Auth\Middleware;

	use Suphle\Request\{PayloadStorage, PathAuthorizer};

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

	class AuthorizeHandler extends CollectibleMiddlewareHandler {

		public function __construct (

			protected readonly PathAuthorizer $pathAuthorizer
		) {

			//
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer { // should use the auth stuff instead of dup

			if (!$this->pathAuthorizer->passesActiveRules()) // still needs major work

				throw new UnauthorizedServiceAccess;

			return $requestHandler->handle($payloadStorage);
		}
	}
?>