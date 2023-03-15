<?php
	namespace Suphle\Contracts\Exception;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Throwable;

	/**
	 * Only generic exceptions are required to define a status code on the exception itself. Those with custom handlers can set it wherever, usually on the handler
	*/
	interface ExceptionHandler {

		public function setContextualData (Throwable $origin):void;

		/**
		 * Action that runs before rendering is done. Renderer that should be flushed for this request should be set here
		*/
		public function prepareRendererData ():void;

		public function getRenderer ():BaseRenderer;
	}
?>