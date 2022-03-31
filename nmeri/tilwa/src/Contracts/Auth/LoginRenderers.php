<?php
	namespace Tilwa\Contracts\Auth;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	interface LoginRenderers {

		public function successRenderer ():BaseRenderer;

		public function failedRenderer ():BaseRenderer;

		public function getLoginService ():LoginActions;
	}
?>