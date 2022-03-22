<?php
	namespace Tilwa\Contracts\Exception;

	use Tilwa\Response\Format\AbstractRenderer;

	use Throwable;

	interface ExceptionHandler {

		public function setContextualData (Throwable $origin):void;

		/**
		 * Action that runs before rendering is done. Renderer that should be flushed for this request should be set here
		*/
		public function prepareRendererData ():void;

		public function getRenderer ():AbstractRenderer;
	}
?>