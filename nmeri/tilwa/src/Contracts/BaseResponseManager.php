<?php

	namespace Tilwa\Http\Response;

	interface BaseResponseManager {
		
		public function getResponse ();

		public function afterRender($data);
	}
?>