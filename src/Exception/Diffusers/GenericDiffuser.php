<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\Exception\{ExceptionHandler, BroadcastableException};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\{ComponentEntry, DetectedExceptionManager};

	use Throwable;

	class GenericDiffuser implements ExceptionHandler {

		protected const CONTROLLER_ACTION = "genericHandler";

		protected BaseRenderer $renderer;

		protected Throwable $origin;

		public function __construct(
			protected readonly RequestDetails $requestDetails,

			protected readonly ComponentEntry $componentEntry,

			protected readonly DetectedExceptionManager $exceptionDetector
		) {

			//
		}

		public function setContextualData (Throwable $origin):void {

			$this->origin = $origin;
		}

		public function prepareRendererData ():void {

			if ($this->origin instanceof BroadcastableException)

				$this->exceptionDetector->queueAlertAdapter(

					$this->origin, $this->requestDetails->getPath()
				);

			if ($this->requestDetails->isApiRoute())

				$this->renderer = $this->getApiRenderer();

			else $this->renderer = $this->getMarkupRenderer();

			$this->renderer->setRawResponse([

				"exception" => $this->origin
			]);

			$incomingCode = $this->origin->getCode();

			$this->renderer->setHeaders(

				(is_int($incomingCode) && $incomingCode > 100) ?

				$incomingCode: 500, []
			);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():BaseRenderer {

			return new Json(self::CONTROLLER_ACTION);
		}

		protected function getMarkupRenderer ():BaseRenderer {

			return (new Markup(self::CONTROLLER_ACTION, "default"))
			
			->setFilePath(
				$this->componentEntry->userLandMirror() . "Markup".

				DIRECTORY_SEPARATOR
			);
		}
	}
?>