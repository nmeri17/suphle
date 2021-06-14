<?php

	namespace Tilwa\Response;

	interface BaseResponseManager {
		
		public function getResponse ();

		public function afterRender($data):void;
	}
?>