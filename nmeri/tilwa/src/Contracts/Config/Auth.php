<?php

	namespace Tilwa\Contracts\Config;

	interface Auth extends ConfigMarker {

		// @return [<string> path => <LoginRenderers> renderer]
		public function getLoginPaths ():array;

		// @return [LoginRenderers] matching path in above array
		public function getPathRenderer (string $path):?string;

		public function getTokenSecretKey ():string;

		public function getTokenIssuer ():string;

		public function getTokenTtl ():int;

		public function isAdmin ($user):bool;

		// [<Model> => <ModelAuthorities>]
		public function getModelObservers():array;
	}
?>