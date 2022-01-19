<?php
	namespace Tilwa\Contracts\Exception;

	use Tilwa\Response\Format\AbstractRenderer;

	interface ExceptionHandler {

		/**
		 * The data, if any, gotten at the point exception was thrown
		*/
		public function setContextualData (array $payload):void;

		/**
		 * Action that runs before rendering is done. Renderer that should be flushed for this request should be set here
		*/
		public function prepareRendererData ():void;

		public function getRenderer ():AbstractRenderer;
	}
?>