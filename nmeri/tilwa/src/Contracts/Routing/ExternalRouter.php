<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	interface ExternalRouter {

		public function canHandleRequest ():bool;

		public function convertToRenderer ():BaseRenderer;
	}
?>