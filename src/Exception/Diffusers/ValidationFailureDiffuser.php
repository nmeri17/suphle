<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer, Requests\ValidationEvaluator};

	use Suphle\Request\PayloadStorage;

	use Suphle\Exception\Explosives\ValidationFailure;

	use Throwable;

	class ValidationFailureDiffuser implements ExceptionHandler {

		public const ERRORS_PRESENCE = "errors",

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

		public function prepareRendererData ():void {

			$this->renderer = $this->validationEvaluator->validationRenderer();

			$this->renderer->setRawResponse(array_merge( // received by the view

				$this->forceArrayResponse(), [

					self::ERRORS_PRESENCE => $this->validationErrors(),

					self::PAYLOAD_KEY => $this->payloadStorage->fullPayload()
				]
			))
			->setHeaders(422, []);
		}

		/**
		* Insurance against routes that can possibly fail validation that don't return an array
		*/
		protected function forceArrayResponse ():array {

			$responseBody = $this->renderer->getRawResponse();

			if (is_array($responseBody)) return $responseBody;

			if (is_iterable($responseBody))

				return json_decode(

					json_encode($responseBody, JSON_THROW_ON_ERROR),

					true, 512, JSON_THROW_ON_ERROR
				);

			return [$responseBody];
		}

		protected function validationErrors ():array {

			return $this->validationEvaluator->getValidatorErrors();
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}
	}
?>