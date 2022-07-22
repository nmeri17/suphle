<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Request\PayloadStorage;

	use Suphle\Request\RequestDetails;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer, Routing\Middleware};

	use Suphle\Auth\Storage\SessionStorage;

	use Suphle\Exception\Explosives\Generic\CsrfException;

	use Suphle\Security\CSRF\CsrfGenerator;

	class CsrfMiddleware implements Middleware {

		private $generator, $authStorage, $requestDetails;

		public function __construct (CsrfGenerator $generator, RequestDetails $requestDetails, AuthStorage $authStorage) {

			$this->generator = $generator;

			$this->authStorage = $authStorage;

			$this->requestDetails = $requestDetails;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$notBrowser = $this->requestDetails->isApiRoute() &&

			!$this->authStorage instanceof SessionStorage;

			if (
				$this->requestDetails->isGetRequest() ||

				$notBrowser
			)

				return $requestHandler->handle($payloadStorage);

			if ( !$this->generator->isVerifiedToken(

				$payloadStorage->getKey(CsrfGenerator::TOKEN_FIELD)
			))

				throw new CsrfException;

			return $requestHandler->handle($payloadStorage);
		}
	}
?>