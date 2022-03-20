<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Hydration\InterfaceCollection, Modules\ControllerModule};

	use Tilwa\Hydration\{Container, Structures\BaseInterfaceCollection};

	use Tilwa\Exception\Explosives\UnexpectedModules;

	use Tilwa\Request\PayloadStorage;

	abstract class ModuleDescriptor {

		protected $container;

		private $expatriates, $hasPreparedExpatriates = false;

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		/**
		 * @param {dependencies} [Interactions\Interface => new ModuleDescriptor]
		*/
		public function sendExpatriates(array $dependencies):void {

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

			return ControllerModule::class;
		}

		/**
		 * Simply bind things into `$this->container`
		*/
		protected function entityBindings ():void {

			//
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

			$this->container->interiorDecorate();
		}

		// this should be on an [ExpatriateManager], but that'll make all the loaded descriptors create new instances of that class
		protected function validateExpatriates ():self {

			$this->deportUnexpected();

			$this->assignModuleShells();

			return $this;
		}

		protected function expatriateHasPreparedExpatriates ():bool {

			return $this->hasPreparedExpatriates;
		}

		protected function empowerExpatriates ():self {

			foreach ($this->expatriates as $descriptor)

				if (!$descriptor->expatriateHasPreparedExpatriates()) // prevent multiple boots

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

				throw new UnexpectedModules($incompatible, get_called_class());
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

			$this->container->setExternalContainerManager();

			$this->entityBindings();

			$this->hasPreparedExpatriates = true;

			$this->validateExpatriates()->empowerExpatriates();

			$this->container->getClass(PayloadStorage::class)->setPayload();

			return $this;
		}

		private function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}
	}
?>