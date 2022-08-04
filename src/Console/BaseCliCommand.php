<?php
	namespace Suphle\Console;

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Hydration\Container;

	use Symfony\Component\Console\Input\{InputOption, InputInterface};

	use Symfony\Component\Console\Command\Command;

	abstract class BaseCliCommand extends Command {

		protected $moduleList, $executionPath, $defaultContainer,

		$withModuleOption = true;

		protected const HYDRATOR_MODULE_OPTION = "hydrating_module";

		public function __construct () {

			parent::__construct(null); // overwriting their constructor to prevent container from sending us an empty string
		}

		public function setModules (array $moduleList):void {

			$this->moduleList = $moduleList;
		}

		public function setExecutionPath (string $path):void {

			$this->executionPath = $path;
		}

		public function setDefaultContainer (Container $container):void {

			$this->defaultContainer = $container;
		}

		/**
		 * Child classes should either call parent::configure() or setName
		*/
		protected function configure ():void {

			$this->setName($this->commandSignature());

			if ($this->withModuleOption)

				$this->addOption(

					self::HYDRATOR_MODULE_OPTION, "m", InputOption::VALUE_OPTIONAL,

					"Module interface to use in hydrating dependencies"
				);
		}

		/**
		 * Using this instead of static::$defaultName since their console runner has the funny logic that ignores the property when defined on a parent class, which means commands can't be replaced by their doubles in a test
		*/
		abstract protected function commandSignature ():string;

		protected function getExecutionContainer (?string $moduleInterface):Container {

			if ($moduleInterface)

				return $this->getActiveModule($moduleInterface)->getContainer();

			if (!empty($this->moduleList))

				return current($this->moduleList)->getContainer();

			return $this->defaultContainer;
		}

		protected function getActiveModule (string $moduleInterface):DescriptorInterface {

			foreach ($this->moduleList as $descriptor)

				if (in_array(
					$descriptor->exportsImplements(),

					class_implements($moduleInterface)
				))

					return $descriptor;
		}
	}
?>