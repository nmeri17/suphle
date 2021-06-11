<?php

	namespace Tilwa\Contracts\Config;

	interface Auth extends ConfigMarker {

		public function getUserModel():string; // this goes once the [NativeAuth] class goes

		// @return [<string> path => <LoginRenderers> renderer]
		public function getLoginPaths ():array;
	}
?>