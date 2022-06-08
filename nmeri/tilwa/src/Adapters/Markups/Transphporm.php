<?php
	namespace Tilwa\Adapters\Markups;

	use Tilwa\Contracts\Presentation\HtmlParser;

	use Tilwa\Contracts\Config\{Transphporm as TConfig, ModuleFiles};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		private $fileConfig, $transphpormConfig;

		public function __construct (ModuleFiles $fileConfig, TConfig $tConfig) {

			$this->fileConfig = $fileConfig;

			$this->transphpormConfig = $tConfig;
		}

		public function parseAll (...$arguments):string {

			$tConfig = $this->transphpormConfig;

			[$markup, $viewModel, $data] = $arguments;

			if ($tConfig->inferFromViewName() && empty($viewModel))

				$viewModel = $markup;

			return (new Builder(
				file_get_contents($this->fileConfig->getViewPath() . $markup . ".php"),

				file_get_contents($tConfig->getTssPath() . $viewModel . ".php")
			))
			->output($data)->body;
		}
	}
?>