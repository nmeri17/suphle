<?php
	namespace Suphle\Contracts\Auth;

	use Suphle\Contracts\{ Modules\HighLevelRequestHandler, Requests\ValidationEvaluator};

	interface ModuleLoginHandler extends HighLevelRequestHandler, ValidationEvaluator {

		public function isValidRequest ():bool;

		public function setResponseRenderer ():self;

		public function processLoginRequest ():void;
	}
?>