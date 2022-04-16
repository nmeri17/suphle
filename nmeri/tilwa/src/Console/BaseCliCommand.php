<?php
	namespace Tilwa\Console;

	use Tilwa\Modules\ModuleDescriptor;

	use Symfony\Component\Console\Input\{InputOption, InputInterface};

	use Symfony\Component\Console\Command\Command;

	class BaseCliCommand extends Command {

		protected $moduleList;

		public function __construct () {

			parent::__construct(null); // overwriting their constructor to prevent container from sending us an empty string
		}

		public function setModules (array $moduleList) {

			$this->moduleList = $moduleList;
		}

		protected function configure ():void {

			$this->addOption(
				"module", "m", InputOption::VALUE_OPTIONAL, "Module interface to use in hydrating dependencies"
			);
		}

		protected function moduleToRun (InputInterface $input):ModuleDescriptor {

			$givenModule = $input->getOption("module");

			if ($givenModule)

				foreach ($this->moduleList as $descriptor)

					if (in_array(
						$descriptor->exportsImplements(),

						class_implements($givenModule)
					))

						return $descriptor;

			return current($this->moduleList);
		}
	}
?>