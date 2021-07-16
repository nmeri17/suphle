<?php
	namespace Tilwa\Adapters\Markups;

	use Tilwa\Contracts\HtmlParser;

	use Tilwa\Contracts\Config\{Transphporm as TConfig, ModuleFiles};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		private $fileConfig, $transConfig;

		public function __construct (ModuleFiles $fileConfig, TConfig $transConfig) {

			$this->fileConfig = $fileConfig;

			$this->transConfig = $transConfig;
		}

		public function parseAll (string $markup, string $viewModel, $data):string {

			return new Builder(
				file_get_contents($this->fileConfig->getViewPath() . $markup),

				file_get_contents($this->transConfig->getTssPath() . $viewModel)
			)
			->output($data)->body;
		}
	}
?>