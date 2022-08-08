<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\MirrorableRenderer;

	class Markup extends BaseTransphpormRenderer implements MirrorableRenderer {

		private $wantsJson;

		public function __construct(string $handler, string $markupName, string $templateName = null) {

			$this->handler = $handler;

			$this->markupName = $markupName;

			$this->templateName = $templateName;

			$this->setHeaders(200, ["Content-Type" => "text/html"]);
		}

		public function render():string {
			
			if ( !$this->wantsJson)

				return $this->htmlParser->parseAll($this);

			$this->setHeaders(200, ["Content-Type" => "application/json"]);

			return $this->renderJson();
		}

		public function setWantsJson ():void {
			
			$this->wantsJson = true;
		}
	}
?>