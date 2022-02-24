<?php
	namespace Tilwa\Contracts\Config;

	use Tilwa\Contracts\Auth\LoginRenderers;

	interface Auth extends ConfigMarker {

		/**
		 * @return LoginRenderer that should handle incoming login request
		*/
		public function getLoginCollection ():?string;		

		/**
		 * @return destination when user hits SessionStorage protected route
		*/
		public function markupRedirect ():string;

		public function getTokenSecretKey ():string;

		public function getTokenIssuer ():string;

		public function getTokenTtl ():int;

		// [<Model> => <ModelAuthorities>]
		public function getModelObservers ():array;
	}
?>