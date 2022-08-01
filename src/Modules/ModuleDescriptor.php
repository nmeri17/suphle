<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\Modules\{DescriptorInterface, ControllerModule};

	use Suphle\Contracts\Hydration\InterfaceCollection;

	use Suphle\Hydration\{Container, ExternalPackageManagerHydrator, Structures\BaseInterfaceCollection};

	use Suphle\Exception\Explosives\Generic\UnexpectedModules;

	abstract class ModuleDescriptor implements DescriptorInterface {

		protected $container, $expatriates = [],

		$hasPreparedExpatriates = false;

		public function __construct (Container $container) {
			
			$this->container = $container;
		}

		/**
		 * @param {dependencies} [Interactions\Interface => new ModuleDescriptor]
		*/
		public function sendExpatriates(array $dependencies):DescriptorInterface {

			$this->expatriates = $dependencies;

			return $this;
		}

		public function getExpatriates ():array {

			return $this->expatriates;
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

		public function globalConcretes ():array {

			return [];
		}

		/**
		 * Bind objects either globally or to specific consumers
		*/
		protected function registerConcreteBindings ():void {

			$bindings = $this->globalConcretes();

			$this->container->whenTypeAny()->needsAny($bindings);
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

			$this->container->setEssentials();

			$this->container->setInterfaceHydrator($this->interfaceCollection());

			$this->container->interiorDecorate();
		}

		// this should be on an [ExpatriateManager], but that'll make all the loaded descriptors create new instances of that class
		protected function validateExpatriates ():self {

			$this->deportUnexpected();

			$this->assignModuleShells();

			return $this;
		}

		public function expatriateHasPreparedExpatriates ():bool {

			return $this->hasPreparedExpatriates;
		}

		protected function empowerExpatriates ():self {

			foreach ($this->expatriates as $descriptor)

				if (!$descriptor->expatriateHasPreparedExpatriates()) {// prevent multiple boots

					$descriptor->warmModuleContainer();

					$descriptor->prepareToRun();
				}

			return $this;
		}

		/**
		 * Doesn't do any hydration; just statically verifies that both lists are compatible
		*/
		private function deportUnexpected ():void {

			$given = array_keys($this->expatriates);

			$expected = $this->expatriateNames();

			$expectedAbsent = array_diff($expected, $given);

			$surplus = array_diff($given, $expected);

			$incompatible = array_merge($expectedAbsent, $surplus);

			if (!empty($incompatible))

				throw new UnexpectedModules($incompatible, get_called_class());
		}

		private function assignModuleShells ():void {

			$collection = $this->container->getClass($this->interfaceCollection());

			$collection->delegateHydrants ($this->expatriates);
		}

		/**
		 * This recursively boots all the lower dependencies. It expects [warmModuleContainer] to have been called first. Both calls aren't coupled together cuz both processes can occur at different times
		*/
		public function prepareToRun ():self {

			$this->registerConcreteBindings(); // this has to come first, since it contains instances crucial to hydration of core objects

			$manager = new ExternalPackageManagerHydrator($this->container);

			$this->container->setExternalContainerManager($manager);

			$this->hasPreparedExpatriates = true;

			$this->validateExpatriates()->empowerExpatriates();

			return $this;
		}
	}
?>