<?php
	namespace Suphle\Contracts\Config;

	use Suphle\Contracts\Auth\LoginFlowMediator;

	interface AuthContract extends ConfigMarker {

		/**
		 * @return LoginMediator that should handle incoming login request
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