<?php
	namespace Tilwa\Contracts\Modules;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	interface HighLevelRequestHandler {

		public function handlingRenderer ():?BaseRenderer;
	}
?>