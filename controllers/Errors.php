<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Errors extends BaseSource {

		public function notFound ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			return [
				'error_url' => @$reqData['error_url'],

				'next_url' => '/'
			];
		}
	}
?>