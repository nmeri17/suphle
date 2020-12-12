<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Reload extends Route {

		public function renderResponse() {

			// derive previous rawResponse and merge that with ours
			$this->rawResponse = $this->getPrevRequest()['data'];

			return $this->publishHtml();
		}
	}
?>