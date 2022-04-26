<?php
	namespace Tilwa\Contracts\Config;

	use Tilwa\Contracts\Auth\LoginRenderers;

	interface AuthContract extends ConfigMarker {

		/**
		 * @return LoginRenderer that should handle incoming login request
		*/
		public function getLoginCollection ():?string;

		/**
		 * @return destination when user hits SessionStorage protected route
		*/
		public function markupRedirect ():string;

		// [<Model> => <ModelAuthorities>]
		public function getModelObservers ():array;

		public function isLoginRequest ():bool;
	}
?>