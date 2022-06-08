<?php
	namespace Tilwa\Console;

	use Tilwa\Modules\ModuleDescriptor;

	use Symfony\Component\Console\Input\{InputOption, InputInterface};

	use Symfony\Component\Console\Command\Command;

	abstract class BaseCliCommand extends Command {

		protected $moduleList;

		public function __construct () {

			parent::__construct(null); // overwriting their constructor to prevent container from sending us an empty string
		}

		public function setModules (array $moduleList) {

			$this->moduleList = $moduleList;
		}

		/**
		 * It's absolutely crucial that parent::configure() is called in all child classes
		*/
		protected function configure ():void {

			$this->setName($this->commandSignature())->addOption(

				"module", "m", InputOption::VALUE_OPTIONAL,

				"Module interface to use in hydrating dependencies"
			);
		}

		/**
		 * Using this instead of static::$defaultName since their console runner has the funny logic that ignores the property when defined on a parent class, which means commands can't be replaced by their doubles in a test
		*/
		abstract protected function commandSignature ():string;

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