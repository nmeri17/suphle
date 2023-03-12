<?php
	namespace Suphle\Adapters\Orms\Eloquent\Middleware;

	use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware, Auth\AuthStorage};

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Request\PayloadStorage;

	use Suphle\Exception\Explosives\{UnverifiedAccount, Unauthenticated};

	class AuthIsVerified implements Middleware {

		protected string $verificationUrl = "/accounts/verify";

		public function __construct (protected readonly AuthStorage $authStorage) {

			//
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$user = $this->authStorage->getUser();

			if (is_null($user))

				throw new Unauthenticated($this->authStorage);

			if (is_null($user->email_verified_at))

				throw new UnverifiedAccount($this->verificationUrl);

			return $requestHandler->handle($payloadStorage);
		}
	}
?>