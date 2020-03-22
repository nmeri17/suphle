<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	class Home extends BaseSource {

		public function index ( array $reqData, array $reqPlaceholders) {

			/*if (!empty($reqData) ) {

				var_dump($reqData); die();}*/

			$userContent = $strangerContent = [];

			if ($this->app->user) $userContent = [['']];

			else $strangerContent = [['']];

			return [$strangerContent, $userContent];
		}
	}

?>