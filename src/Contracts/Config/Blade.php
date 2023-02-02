<?php
	namespace Suphle\Contracts\Config;

	use Illuminate\Contracts\View\Factory as BladeViewFactoryInterface;

	interface Blade extends ConfigMarker {

		/**
		 * Where present, this must be called before setViewFactory
		*/
		public function addViewPath (string $markupPath):void;

		public function getViewFactory ():BladeViewFactoryInterface;

		public function setViewFactory ():void;
	}
?>