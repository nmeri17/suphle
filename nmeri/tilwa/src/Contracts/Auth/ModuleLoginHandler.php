<?php
	namespace Tilwa\Contracts\Auth;

	use Tilwa\Contracts\{ Modules\HighLevelRequestHandler, Requests\ValidationEvaluator};

	interface ModuleLoginHandler extends HighLevelRequestHandler, ValidationEvaluator {

		public function isValidRequest ():bool;
		
		public function getResponse ();
	}
?>