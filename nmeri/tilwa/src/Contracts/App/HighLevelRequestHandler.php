<?php
	namespace Tilwa\Contracts\App;

	use Tilwa\Response\Format\AbstractRenderer;

	interface HighLevelRequestHandler {

		public function handlingRenderer ():AbstractRenderer;
	}
?>