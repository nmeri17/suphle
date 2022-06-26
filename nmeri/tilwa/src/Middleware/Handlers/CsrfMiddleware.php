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