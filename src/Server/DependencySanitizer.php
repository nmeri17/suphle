<?php
	namespace Suphle\Server;

	use Suphle\Contracts\{Config\ModuleFiles, Modules\ControllerModule};

	use Suphle\IO\Http\BaseHttpRequest;

	use Suphle\Request\PayloadStorage;

	use Suphle\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Symfony\Component\Console\Output\ConsoleOutput;

	use Arkitect\ClassSet;

	use Arkitect\CLI\{Config, Runner, TargetPhpVersion, Progress\ProgressBarProgress};
	
	use Arkitect\Exceptions\FailOnFirstViolationException;

	use Arkitect\Rules\{Violations, Rule};

	use Arkitect\Expression\ForClasses\{Extend, DependsOnlyOnTheseNamespaces, NotDependsOnTheseNamespaces, ResideInOneOfTheseNamespaces, NotHaveDependencyOutsideNamespace, HaveNameMatching};

	use Throwable;

	class DependencySanitizer {

		protected ?Violations $violations = null;

		public function __construct (private readonly ModuleFiles $fileConfig) {

			//
		}

		public function cleanseConsumers ():bool {

			return $this->scanModuleContents(

				$this->customizeConfig(new Config), new ConsoleOutput
			);
		}

		protected function scanModuleContents (Config $config, OutputInterface $output):bool {

			$runner = new Runner();
			
			try {
			
				$runner->run(

					$config, new ProgressBarProgress($output),

					TargetPhpVersion::create(null)
				);
			}
			catch (Throwable $exception) {
			
				$output->writeln($exception->getMessage());
			}
			
			$this->violations = $runner->getViolations();
			
			if ($this->violations->count() > 0) {
				
				$this->printViolations($output);

				return false;
			}

			return true;
		}

		protected function printViolations(OutputInterface $output): void {

			$output->writeln('<error>ERRORS!</error>');

			$output->writeln(sprintf('%s', $this->violations->toString()));

			$output->writeln(sprintf('<error>%s VIOLATIONS DETECTED!</error>', count($this->violations)));
		}

		protected function customizeConfig (Config $config):Config {

			$modulePath = $this->fileConfig->activeModulePath();

			$moduleClassSet = ClassSet::fromDir($modulePath);

			$rules = [

				$this->restrictCoordinator(),

				...$this->protectUpdateyServices(),

				$this->lockModuleFromSiblings($modulePath)
			];

			$config->add($moduleClassSet, ...$rules);
		}

		protected function restrictCoordinator () {

			return Rule::allClasses()
			
			->that(new Extend(ServiceCoordinator::class))

			->should(
				new DependsOnlyOnTheseNamespaces(...[
					ConditionalFactory::class, // We're treating it as a type of service in itself
					ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

					PayloadStorage::class, // there may be items we don't want to pass to the builder but to a service?

					BaseHttpRequest::class, UpdatefulService::class,

					UpdatelessService::class
				], // constructor arguments
				...[

					ModelfulPayload::class, ModellessPayload::class
				]) // action method arguments
			);
		}

		protected function protectUpdateyServices ():array {

			$updatefulRule = Rule::allClasses()
			
			->that(new Extend(UpdatefulService::class))

			->should(new NotDependsOnTheseNamespaces(UpdatelessService::class));

			$updatelessRule = Rule::allClasses()
			
			->that(new Extend(UpdatelessService::class))

			->should(new NotDependsOnTheseNamespaces(UpdatefulService::class));

			return [$updatefulRule, $updatelessRule];
		}

		protected function lockModuleFromSiblings (string $modulePath) {

			$moduleNamespace = "*" . basename($modulePath);

			return Rule::allClasses()

			->that(new ResideInOneOfTheseNamespaces( $moduleNamespace))
			
			->should(new NotHaveDependencyOutsideNamespace($moduleNamespace))

			->except(new HaveNameMatching("*Interactions", "*Models"));
		}

		public function getViolations ():?Violations {

			return $this->violations;
		}
	}
?>