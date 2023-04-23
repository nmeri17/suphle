<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer, Requests\ValidationEvaluator};

	use Suphle\Request\{PayloadStorage, RequestDetails};

	use Suphle\Exception\Explosives\ValidationFailure;

	use Throwable;

	class ValidationFailureDiffuser implements ExceptionHandler {

		public const ERRORS_PRESENCE = "validation_errors",

		PAYLOAD_KEY = "payload_storage",

		FAILURE_KEYS = [self::ERRORS_PRESENCE, self::PAYLOAD_KEY];

		protected BaseRenderer $renderer;

		protected ValidationEvaluator $validationEvaluator;

		public function __construct (

			protected readonly PayloadStorage $payloadStorage,

			protected readonly RequestDetails $requestDetails
		) {

			//
		}

		/**
		 * @param {origin} ValidationFailure
		*/
		public function setContextualData (Throwable $origin):void {

			$this->validationEvaluator = $origin->getEvaluator();
		}

		/**
		 * Expected to be called before renderer->render()
		*/
		public function prepareRendererData ():void {

			$this->renderer = $this->validationEvaluator->validationRenderer([ // received by the view

				self::ERRORS_PRESENCE => $this->validationErrors(),

				self::PAYLOAD_KEY => $this->payloadStorage->fullPayload()
			]);

			if ($this->validationEvaluator->shouldSetCode(

				$this->requestDetails, $this->renderer
			))

				$this->renderer->setHeaders(422, []);
		}

		protected function validationErrors ():array {

			return $this->validationEvaluator->getValidatorErrors();
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}
	}
?>