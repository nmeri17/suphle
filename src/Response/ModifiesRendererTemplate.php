<?php
	namespace Suphle\Response;

	use Suphle\Contracts\Presentation\RendersMarkup;

	trait ModifiesRendererTemplate {

		protected function setMarkupDetails ():void {

			if (!$this->renderer instanceof RendersMarkup) return;

			$this->renderer->setMarkupName($this->newMarkupName);
			
			$this->htmlParser->findInPath(
				$this->componentEntry->userLandMirror() . "Markup".

				DIRECTORY_SEPARATOR
			);
		}
	}
?>