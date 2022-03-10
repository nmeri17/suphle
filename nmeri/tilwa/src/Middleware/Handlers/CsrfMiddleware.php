<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Auth\Storage\SessionStorage;

	use Tilwa\Exception\Explosives\Generic\CsrfException;

	use Tilwa\Security\CSRF\CsrfGenerator;

	class CsrfMiddleware extends BaseMiddleware {

		private $generator, $requestDetails, $authStorage;

		public function __construct (CsrfGenerator $generator, RequestDetails $requestDetails, AuthStorage $authStorage) {

			$this->generator = $generator;

			$this->requestDetails = $requestDetails;

			$this->authStorage = $authStorage;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			if ($this->requestDetails->isGetRequest() )

				return $requestHandler->handle($payloadStorage);

			$isBrowserRoute = !$this->requestDetails->isApiRoute();

			$usingSession = $this->authStorage instanceof SessionStorage;

			$incomingToken = !$this->generator->isVerifiedToken($payloadStorage->getKey(CsrfGenerator::TOKEN_FIELD));

			if ($isBrowserRoute && $usingSession && $incomingToken)

				throw new CsrfException;

			return $requestHandler->handle($payloadStorage);
		}
	}
?>