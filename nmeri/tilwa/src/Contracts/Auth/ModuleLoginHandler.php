<?php
	namespace Tilwa\Contracts\Auth;

	use Tilwa\Contracts\{ Modules\HighLevelRequestHandler, Request\ValidationEvaluator};

	interface ModuleLoginHandler extends HighLevelRequestHandler, ValidationEvaluator {

		public function isValidRequest ():bool;
		
		public function getResponse ();

		public function isLoginRequest ():bool;

		public function setAuthService ():void;
	}
?>