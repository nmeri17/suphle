<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\MirrorableRenderer;

	class Markup extends BaseTransphpormRenderer implements MirrorableRenderer {

		private $wantsJson;

		/**
		 * @param {markupName}: It's not necessary to prefix with a slash
		*/
		public function __construct(

			protected string $handler, 

			protected string $markupName, 

			protected ?string $templateName = null
		) {

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