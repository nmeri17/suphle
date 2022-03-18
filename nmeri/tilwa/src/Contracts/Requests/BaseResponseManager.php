<?php
	namespace Tilwa\Contracts\Requests;

	interface BaseResponseManager {
		
		public function getResponse ();

		public function afterRender ($data):void;
	}
?>