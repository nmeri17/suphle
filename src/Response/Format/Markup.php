<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\MirrorableRenderer;

	use Suphle\Request\PayloadStorage;

	/*
	 * Should not be used in conjuction with forms. Form actions should leave the request's originator
	*/
	class Markup extends BaseTransphpormRenderer implements MirrorableRenderer {

		protected bool $wantsJson = false;

		/**
		 * @param {markupName}: It's not necessary to prefix with a slash
		*/
		public function __construct(

			protected string $handler, protected string $markupName, 

			protected ?string $templateName = null
		) {

			$this->setHeaders(200, [

				PayloadStorage::CONTENT_TYPE_KEY => "text/html"
			]);
		}

		public function render():string {
			
			if ( !$this->wantsJson)

				return $this->htmlParser->parseAll($this);

			$this->setHeaders(200, [

				PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
			]);

			return $this->renderJson();
		}

		public function setWantsJson ():void {
			
			$this->wantsJson = true;
		}
	}
?>