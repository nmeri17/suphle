<?php
	namespace Tilwa\Contracts\Config;

	interface Auth extends ConfigMarker {

		/**
		 * @return [<string> path => <LoginRenderers> renderer]
		public function getLoginPaths ():array;
		*/

		/**
		 * @return destination when user hits SessionToken protected route
		*/
		public function markupRedirect ():string;

		public function getTokenSecretKey ():string;

		public function getTokenIssuer ():string;

		public function getTokenTtl ():int;

		// [<Model> => <ModelAuthorities>]
		public function getModelObservers():array;
	}
?>