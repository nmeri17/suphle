<?php
	namespace Suphle\Response;

	use Suphle\Modules\ModuleHandlerIdentifier;

	use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

	class PreviousResponse {

		public const PREVIOUS_GET_PATH = "previous_get_path";

		public function __construct (

			protected readonly ModuleHandlerIdentifier $handlerIdentifier,

			protected readonly Session $sessionClient
		) {

			//
		}

		public function getRenderer ():BaseRenderer {

			$previousPath = $this->sessionClient->getValue(self::PREVIOUS_GET_PATH);

			// internally handle request. A more convenient alternative is to store the renderer itself but I fear it could pose a security risk or a large response size could exceed permitted session contents
			$this->handlerIdentifier->setRequestPath($previousPath); // think this is dangerous cuz it flushes objects and switches context to this new path

			$this->handlerIdentifier->handleGenericRequest();

			return $this->handlerIdentifier->underlyingRenderer();
		}

		public function setPreviousGetPath (string $path, array $queryParameters):void {

			$this->sessionClient->setValue(

				self::PREVIOUS_GET_PATH,

				$path . "?". http_build_query($queryParameters)
			);
		}
	}
?>