<?php

	namespace Tilwa\Contracts;

	use Tilwa\Response\Format\AbstractRenderer;

	interface LoginRenderers {

		public function successRenderer ():AbstractRenderer;

		public function failedRenderer ():AbstractRenderer;

		public function getLoginService ():LoginActions;
	}
?>