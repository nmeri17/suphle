<?php
	namespace Suphle\Response;

	use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

	use Suphle\Request\RequestDetails;

	class PreviousResponse {

		public const PREVIOUS_GET_RENDERER = "previous_get_renderer";

		public function __construct (

			protected readonly RoutedRendererManager $rendererManager,

			protected readonly BaseRenderer $renderer
		) {

			//
		}

		public function invokeRenderer (array $toMerge = []):BaseRenderer {

			if (!$this->renderer->deferValidationContent()) // if current request is something like json, write validation errors to it

				return $this->renderer;

			$previousRenderer = $this->sessionClient->getValue(self::PREVIOUS_GET_RENDERER);

			$this->rendererManager->bypassRendererProtocols($previousRenderer);
			
			$previousRenderer->forceArrayShape($toMerge);

			return $previousRenderer;
		}
	}
?>