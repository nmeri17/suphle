<?php
	namespace Suphle\Adapters\Markups;

	use Suphle\Contracts\Presentation\HtmlParser;

	use Suphle\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		private $fileConfig, $viewConfig;

		public function __construct (ModuleFiles $fileConfig, ViewConfig $viewConfig) {

			$this->fileConfig = $fileConfig;

			$this->viewConfig = $viewConfig;
		}

		public function parseAll (...$arguments):string {

			$viewConfig = $this->viewConfig;

			[$markup, $template, $data] = $arguments;

			$markup = trim($markup, "/");

			if (empty($template) && $viewConfig->inferFromViewName())

				$template = $markup;

			else $template = trim($template, "/");

			return (new Builder(
				$this->readFile($this->fileConfig->getViewPath() . $markup),

				$this->readFile($viewConfig->getTssPath() . $template)
			))
			->output($data)->body;
		}

		public function readFile (string $fileName):string {

			return file_get_contents("$fileName.php");
		}
	}
?>