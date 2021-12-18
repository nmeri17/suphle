<?php
	namespace Tilwa\App;

	use Tilwa\Contracts\Config\{Auth as IAuth, Services as IServices, Transphporm as ITransphporm, Laravel as ILaravel, Orm as IOrm};

	use Tilwa\Config\{Auth, Services, Transphporm, Laravel, Orm};

	use Tilwa\Contracts\Auth\UserHydrator as HydratorContract;

	use Tilwa\Auth\Models\Eloquent\UserHydrator;

	use Tilwa\Errors\{InvalidModuleImport, UnexpectedModules};

	abstract class ModuleDescriptor {

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

		public function expatriateNames():array {

			return [];
		}

		/**
		 * @return concrete implementing `exportsImplements`
		*/
		abstract public function exports():object;

		/**
		 * Interface from Interactions namespace which will be consumers API with this module
		*/
		abstract public function exportsImplements():string;

		/**
		 * Arguments will be auto-wired
		*/
		protected function entityBindings ():self {

			return $this;
		}

		public function getContainer():Container {
			
			return $this->container;
		}

		protected function getConfigs():array {
			
			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IAuth::class => Auth::class,

				ITransphporm::class => Transphporm::class,

				HydratorContract::class => UserHydrator::class
			];
		}

		public function absorbConfigs ():void {

			$this->container->setConfigs($this->getConfigs())
		}

		// this should be on its own class, but that'll make all the loaded descriptors create new instances of that ExpatriateManager
		protected function validateExpatriates ():self {

			$this->deportUnexpected();

			$this->container->whenTypeAny()->needsAny($this->getModuleShells());

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

		private function getModuleShells ():array {

			$shells = [];

			foreach ($dependencies as $contract => $concrete) {

				$service = $concrete->exports();

				$compatible = $contract == $concrete->exportsImplements();

				$correctExport = $service instanceof $contract;
				
				if (!($compatible && $correctExport))

					throw new InvalidModuleImport($contract);

				$shells[$contract] = $service;
			}

			return $shells;
		}

		/**
		 * This recursively boots all the lower dependencies
		*/
		public function prepareToRun ():self {

			$this->provideSelf();

			$customBindings = $this->container->getMethodParameters("entityBindings", get_class($this)); // get the bindings on the actively running sub-class

			$this->entityBindings(...array_values($customBindings))

			->validateExpatriates()->empowerExpatriates();

			return $this;
		}

		private function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}
	}
?>