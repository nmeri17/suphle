<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer, Requests\ValidationEvaluator};

	use Suphle\Request\PayloadStorage;

	use Suphle\Exception\Explosives\ValidationFailure;

	use Throwable;

	class ValidationFailureDiffuser implements ExceptionHandler {

		public const ERRORS_PRESENCE = "validation_errors",

		PAYLOAD_KEY = "payload_storage";

		protected BaseRenderer $renderer;

		protected ValidationEvaluator $validationEvaluator;

		public function __construct (

			protected readonly PayloadStorage $payloadStorage
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