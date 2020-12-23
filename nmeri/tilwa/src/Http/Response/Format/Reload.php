<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Reload extends Route {

		public function renderResponse(HtmlParser $htmlAdapter) {

			$this->rawResponse += $this->getPrevious()->rawResponse; // avoid overwriting our own response
			
			// you want to call this->runViewModels somewhere here
			return $this->publishHtml($htmlAdapter);
		}
	}
?>