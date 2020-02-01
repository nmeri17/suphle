<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Home extends BaseSource {

		public function index ( string $urlSlug, array $rsxData) {

			return ['content' => "this is dynamic content", []];
		}
	}

?>