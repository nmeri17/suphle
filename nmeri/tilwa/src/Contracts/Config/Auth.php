<?php

	namespace Tilwa\Contracts\Config;

	interface Auth extends ConfigMarker {

		// @return [<string> path => <LoginRenderers> renderer]
		public function getLoginPaths ():array;

		public function getPathRenderer (string $path):LoginRenderers;

		public function getTokenSecretKey () ():string;

		public function getTokenIssuer ():string;

		public function getTokenTtl ():int;

		public function isAdmin ($user):bool;
	}
?>