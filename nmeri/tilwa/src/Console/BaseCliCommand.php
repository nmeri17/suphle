<?php
	namespace Tilwa\Console;

	use Tilwa\Modules\ModuleDescriptor;

	use Symfony\Component\Console\{Command\Command, Input\InputInterface};

	class BaseCliCommand extends Command {

		protected $moduleList;

		public function setModules (array $moduleList) {

			$this->moduleList = $moduleList;
		}

		protected function configure ():void {

			$this->addOption(
				"module", "m", InputOption::OPTIONAL, "Module interface to use in hydrating dependencies"
			);
		}

		protected function moduleToRun (InputInterface $input):ModuleDescriptor {

			$givenModule = $input->getOption("module");

			if ($givenModule)

				foreach ($this->moduleList as $descriptor)

					if ($givenModule instanceof $descriptor->exportsImplements())

						return $descriptor;

			return current($this->moduleList);
		}
	}
?>