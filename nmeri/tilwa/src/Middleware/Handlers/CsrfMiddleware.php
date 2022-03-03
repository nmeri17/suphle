<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Exception\Explosives\Generic\CsrfException;

	use Tilwa\Security\CSRF\CsrfGenerator;

	class CsrfMiddleware extends BaseMiddleware {

		private $generator;

		public function __construct (CsrfGenerator $generator, RequestDetails $requestDetails) {

			$this->generator = $generator;

			$this->requestDetails = $requestDetails;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			if ($this->requestDetails->isGetRequest())

				return $requestHandler->handle($payloadStorage);

			if (
				!$payloadStorage->hasKey(CsrfGenerator::TOKEN_FIELD) ||

				!$this->generator->isVerifiedToken($payloadStorage->getKey(CsrfGenerator::TOKEN_FIELD))
			)

				throw new CsrfException;

			return $requestHandler->handle($payloadStorage);
		}
	}
?>