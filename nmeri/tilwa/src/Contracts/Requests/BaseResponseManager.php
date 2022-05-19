<?php
	namespace Tilwa\Contracts\Requests;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	interface BaseResponseManager {
		
		public function responseRenderer ():BaseRenderer;

		public function afterRender ($data):void;
	}
?>