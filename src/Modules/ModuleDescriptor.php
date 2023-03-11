<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\Modules\{DescriptorInterface, ControllerModule};

	use Suphle\Contracts\{Hydration\InterfaceCollection, Database\OrmDialect};

	use Suphle\Hydration\{Container, ExternalPackageManagerHydrator};

	use Suphle\Hydration\Structures\{BaseInterfaceCollection, ContainerBooter, ObjectDetails};

	use Suphle\Exception\Explosives\DevError\UnexpectedModules;

	abstract class ModuleDescriptor implements DescriptorInterface {

		protected array $expatriates = [];

		protected bool $hasPreparedExpatriates = false;

		public function __construct (protected readonly Container $container) {
			
			//
		}

		/**
		 * @param {dependencies} [Interactions\Interface => new ModuleDescriptor]
		*/
		public function sendExpatriates(array $dependencies):DescriptorInterface {

			$this->expatriates = $dependencies;

			return $this;
		}

		/**
		 * @return Interfaces implemented by sibling modules that this module requires to function
		*/
		public function expatriateNames ():array {

			return [];
		}

		public function materialize () {

			return $this->container->getClass($this->exportsImplements());
		}

		/**
		 * {@inheritdoc}
		*/
		public function exportsImplements ():string {

			return ControllerModule::class;
		}

		/**
		 * Binding is unnecessary here. Just return the pairs
		*/
		public function globalConcretes ():array {

			return [];
		}

		/**
		 * Bind objects to all or specific consumers. Or, trigger a component's booting by pulling it from the container
		*/
		protected function registerConcreteBindings ():void {

			$bindings = $this->globalConcretes();

			$this->container->whenTypeAny()->needsAny($bindings)

			->getClass(OrmDialect::class); // without forcing an ORM hydration using our config, this module's laravel container will create a random, unconfigured db accessor object that will take the place of any existing connection
		}

		public function getContainer():Container {
			
			return $this->container;
		}

		/**
		 * @return Class implementing InterfaceCollection
		*/
		public function interfaceCollection ():string {

			return BaseInterfaceCollection::class;
		}

		public function warmModuleContainer ():void {

			(new ContainerBooter($this->container ))

			->initializeContainer($this->interfaceCollection());
		}

		public function getExpatriates ():array {

			$this->validateExpatriates();

			$expatriates = array_filter($this->expatriates, function ($descriptor) {

				return !$descriptor->expatriateHasPreparedExpatriates(); // prevent multiple boots
			});

			return $expatriates;
		}

		// this should be on an [ExpatriateManager], but that'll make all the loaded descriptors create new instances of that class
		protected function validateExpatriates ():void {

			$this->deportUnexpected();

			$this->assignModuleShells();
		}

		public function expatriateHasPreparedExpatriates ():bool {

			return $this->hasPreparedExpatriates;
		}

		/**
		 * Doesn't do any hydration; just statically verifies that both lists are compatible
		*/
		private function deportUnexpected ():void {

			$given = array_keys($this->expatriates);

			$expected = $this->expatriateNames();

			$objectMeta = $this->container->getClass(ObjectDetails::class);

			$expectedAbsent = array_filter($expected, function ($descriptorName) use ( $objectMeta) {

				foreach ($this->expatriates as $descriptor) {

					if ($objectMeta->stringInClassTree(

						$descriptor->exportsImplements(), $descriptorName
					))

						return false;
				}

				return true;
			});

			$surplus = array_diff($given, $expected);

			$incompatible = array_merge($expectedAbsent, $surplus);

			if (!empty($incompatible))

				throw new UnexpectedModules($incompatible, static::class);
		}

		private function assignModuleShells ():void {

			$collection = $this->container->getClass($this->interfaceCollection());

			$collection->delegateHydrants ($this->expatriates);
		}

		/**
		 * It expects [warmModuleContainer] to have been called first. Both calls aren't coupled together cuz both processes can occur at different times
		*/
		public function prepareToRun ():self {

			if ($this->hasPreparedExpatriates)

				return $this; // avoid overwriting booted bindings

			$this->registerConcreteBindings(); // this has to come first, since it contains instances crucial to hydration of core objects

			$this->container->setExternalContainerManager(

				new ExternalPackageManagerHydrator($this->container)
			);

			$this->hasPreparedExpatriates = true;

			return $this;
		}
	}
?>