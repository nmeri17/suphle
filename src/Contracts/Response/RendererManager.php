<?php
	namespace Suphle\Contracts\Response;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\Presentation\BaseRenderer;

	interface RendererManager {

		public function bootDefaultRenderer ():self;

		public function handleValidRequest (PayloadStorage $payloadStorage):BaseRenderer;

		public function bypassOrganicProcedures (BaseRenderer $renderer, bool $skipValidation = false):void;

		public function mayBeInvalid ():void;

		public function invokePreviousRenderer (array $toMerge = []):BaseRenderer;

		public function updateValidatorMethod (ServiceCoordinator $coodinator, string $handlingMethod):void;
	}
?>