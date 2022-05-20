<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Auth\Storage\SessionStorage;

	use Tilwa\Exception\Explosives\Generic\CsrfException;

	use Tilwa\Security\CSRF\CsrfGenerator;

	class CsrfMiddleware implements Middleware {

		private $generator, $authStorage, $requestDetails;

		public function __construct (CsrfGenerator $generator, RequestDetails $requestDetails, AuthStorage $authStorage) {

			$this->generator = $generator;

			$this->authStorage = $authStorage;

			$this->requestDetails = $requestDetails;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			if ($this->requestDetails->isGetRequest() )

				return $requestHandler->handle($payloadStorage);

			$isBrowserRoute = !$this->requestDetails->isApiRoute();

			$usingSession = $this->authStorage instanceof SessionStorage;

			$failedVerification = !$this->generator->isVerifiedToken(

				$payloadStorage->getKey(CsrfGenerator::TOKEN_FIELD)
			);

			if ($isBrowserRoute && $usingSession && $failedVerification)

				throw new CsrfException;

			return $requestHandler->handle($payloadStorage);
		}
	}
?>