<?php
	namespace Suphle\Contracts\Response;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Exception\Explosives\{ValidationFailure, Generic\NoCompatibleValidator};

	interface RendererManager {

		public function bootDefaultRenderer ():self;

		public function handleValidRequest (PayloadStorage $payloadStorage):BaseRenderer;

		public function fetchHandlerParameters (

			ServiceCoordinator $coodinator, string $handlingMethod,

			bool $skipValidation = false
		):array;

		/**
		 * @throws ValidationFailure
		*/
		public function mayBeInvalid ():void;

		public function invokePreviousRenderer (array $toMerge = []):BaseRenderer;

		/**
		 * @throws NoCompatibleValidator
		*/
		public function updateValidatorMethod (ServiceCoordinator $coodinator, string $handlingMethod):void;
	}
?>