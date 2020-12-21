<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Reload extends Route {

		public function renderResponse() {

			$this->rawResponse += $this->getPrevious()->rawResponse; // avoid overwriting our own response

			return $this->publishHtml();
		}
	}
?>