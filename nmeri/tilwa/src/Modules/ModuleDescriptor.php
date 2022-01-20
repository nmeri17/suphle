<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Hydration\InterfaceCollection, App\BlankModule};

	use Tilwa\Hydration\{Container, BaseInterfaceCollection};

	use Tilwa\Exception\Explosives\UnexpectedModules;

	use Tilwa\Request\PayloadStorage;

	class ModuleDescriptor {

		protected $container;

		private $expatriates;

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		/**
		 * @param {dependencies} [Interactions\Interface => new ModuleDescriptor]
		*/
		public function sendExpatriates(array $dependencies):self {

			$this->expatriates = $dependencies;
		}

		public function getExpatriates():array {

			return $this->expatriates;
		}

		/**
		 * @return Interfaces implemented by sibling modules that this module requires to function
		*/
		public function expatriateNames():array {

			return [];
		}

		public function materialize () {

			return $this->container->getClass($this->exportsImplements());
		}

		/**
		 * Interface which will be consumers' API on this module
		*/
		public function exportsImplements():string {

			return BlankModule::class;
		}

		/**
		 * Arguments will be auto-wired
		*/
		protected function entityBindings ():self {

			return $this;
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

		public function warmUp ():void {

			$this->container->provideSelf();

			$this->container->setInterfaceHydrator($this->interfaceCollection());
		}

		// this should be on an [ExpatriateManager], but that'll make all the loaded descriptors create new instances of that class
		protected function validateExpatriates ():self {

			$this->deportUnexpected();

			$this->assignModuleShells();

			return $this;
		}

		protected function empowerExpatriates ():self {

			foreach ($this->expatriates as $descriptor)

				$descriptor->prepareToRun();

			return $this;
		}

		private function deportUnexpected ():void {

			$given = array_keys($this->expatriates);

			$expected = $this->expatriateNames();

			$expectedAbsent = array_diff($expected, $given);

			$surplus = array_diff($given, $expected);

			$incompatible = array_merge($expectedAbsent, $surplus);

			if (!empty($incompatible))

				throw new UnexpectedModules($incompatible, get_class($this));
		}

		private function assignModuleShells ():void {

			$collection = $this->container->getClass($this->interfaceCollection());

			$collection->delegateHydrants ($this->expatriates);
		}

		/**
		 * This recursively boots all the lower dependencies
		*/
		public function prepareToRun ():self {

			$this->provideSelf();

			$customBindings = $this->container->getMethodParameters("entityBindings", get_class($this)); // get the bindings on the actively running sub-class

			$this->entityBindings(...array_values($customBindings))

			->validateExpatriates()->empowerExpatriates();

			$this->container->getClass(PayloadStorage::class)->setPayload();

			return $this;
		}

		private function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}
	}
?>