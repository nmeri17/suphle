<?php
	namespace Tilwa\Adapters\Markups;

	use Tilwa\Contracts\Presentation\HtmlParser;

	use Tilwa\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		private $fileConfig, $viewConfig;

		public function __construct (ModuleFiles $fileConfig, ViewConfig $viewConfig) {

			$this->fileConfig = $fileConfig;

			$this->viewConfig = $viewConfig;
		}

		public function parseAll (...$arguments):string {

			$viewConfig = $this->viewConfig;

			[$markup, $viewModel, $data] = $arguments;

			if (empty($viewModel) && $viewConfig->inferFromViewName())

				$viewModel = $markup;

			return (new Builder(
				$this->readFile($this->fileConfig->getViewPath() . $markup),

				$this->readFile($viewConfig->getTssPath() . $viewModel)
			))
			->output($data)->body;
		}

		public function readFile (string $fileName):string {

			return file_get_contents("$fileName.php");
		}
	}
?>