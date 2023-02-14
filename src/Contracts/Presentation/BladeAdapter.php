<?php
	namespace Suphle\Contracts\Presentation;

	/**
	 * Not actually used by a consumer but provides a contract of expectations required to make the library usable
	*/
	interface BladeAdapter extends HtmlParser {

		public function setViewFactory ():void;

		public function bindComponentTags ():void;
	}
?>