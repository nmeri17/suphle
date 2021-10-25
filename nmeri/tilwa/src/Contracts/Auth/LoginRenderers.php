<?php

	namespace Tilwa\Contracts\Auth;

	use Tilwa\Response\Format\AbstractRenderer;

	interface LoginRenderers {

		public function successRenderer ():AbstractRenderer;

		public function failedRenderer ():AbstractRenderer;

		public function getLoginService ():LoginActions;
	}
?>