<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Home extends BaseSource {

		public function index ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			return ['content' => "this is dynamic content", []];
		}
	}

?>