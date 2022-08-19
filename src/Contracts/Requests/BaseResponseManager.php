<?php
	namespace Suphle\Contracts\Requests;

	use Suphle\Contracts\Presentation\BaseRenderer;

	interface BaseResponseManager {
		
		public function responseRenderer ():BaseRenderer;

		public function afterRender ($data):void;
	}
?>