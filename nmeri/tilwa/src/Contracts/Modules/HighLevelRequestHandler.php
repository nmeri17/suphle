<?php
	namespace Tilwa\Contracts\Modules;

	use Tilwa\Response\Format\AbstractRenderer;

	interface HighLevelRequestHandler {

		public function handlingRenderer ():AbstractRenderer;
	}
?>