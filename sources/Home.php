<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Home extends BaseSource {

		public function main ( string $urlSlug, array $rsxData) {

			return ['data', func_get_args()];
		}
	}

?>