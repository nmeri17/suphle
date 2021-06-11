<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	use Tilwa\Auth\BrowserLoginRenderer;

	class Auth implements AuthContract {

		public function getLoginPaths ():array {

			return [
				"login" => BrowserLoginRenderer::class
			];
		}
	}
?>