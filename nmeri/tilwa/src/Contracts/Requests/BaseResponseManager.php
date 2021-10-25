<?php

	namespace Tilwa\Response\Requests;

	interface BaseResponseManager {
		
		public function getResponse ();

		public function afterRender($data):void;
	}
?>