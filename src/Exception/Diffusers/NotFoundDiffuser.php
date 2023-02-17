<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\Exception\ExceptionHandler;

	use Suphle\Contracts\Presentation\{HtmlParser, BaseRenderer};

	use Suphle\Hydration\DecoratorHydrator;

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\{ComponentEntry, Explosives\NotFoundException};

	use Throwable;

	class NotFoundDiffuser implements ExceptionHandler {

		protected BaseRenderer $renderer;

		public function __construct(
			protected readonly RequestDetails $requestDetails,

			protected readonly ComponentEntry $componentEntry,

			protected readonly DecoratorHydrator $decoratorHydrator,

			protected readonly HtmlParser $htmlParser
		) {

			//
		}

		/**
		 * @param {origin} NotFoundException
		*/
		public function setContextualData (Throwable $origin):void {

			//
		}

		public function prepareRendererData ():void {

			if ($this->requestDetails->isApiRoute())

				$this->renderer = new Json("");

			else $this->renderer = $this->getMarkupRenderer();

			$url = $this->requestDetails->getPath();

			$this->renderer->setRawResponse([

				"url" => $url,

				"message" => $url . " Not Found"
			])->setHeaders(404, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getMarkupRenderer ():BaseRenderer {

			$renderer = new Markup("missingHandler", "not-found");

			$this->decoratorHydrator->scopeInjecting(

				$renderer, self::class
			);

			$this->htmlParser->findInPath(
				$this->componentEntry->userLandMirror() . "Markup".

				DIRECTORY_SEPARATOR
			);

			return $renderer;
		}
	}
?>