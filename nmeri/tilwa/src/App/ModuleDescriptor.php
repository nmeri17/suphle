<?php

	namespace Tilwa\App;

	use Tilwa\Contracts\Config\{Auth as IAuth, Services as IServices, Transphporm as ITransphporm, Laravel as ILaravel, Orm as IOrm};

	use Tilwa\Config\{Auth, Services, Transphporm, Laravel, Orm};

	use Tilwa\Contracts\Auth\UserHydrator as HydratorContract;

	use Tilwa\Auth\Models\Eloquent\UserHydrator;

	abstract class ModuleDescriptor {

		protected $container;

		private $dependsOn;

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		public function setDependsOn(array $dependencies):self {
			
			foreach ($dependencies as $contract => $concrete) {

				$service = $concrete->exports();
				
				if ($contract == $concrete->exportsImplements() && $service instanceof $contract) {

					$pair = [$contract => $service];

					$this->dependsOn = array_merge($this->dependsOn, $pair);

					$this->container->whenTypeAny()->needsAny($pair);
				}
			}
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return $this->dependsOn;
		}

		// @return concrete implementing `exportsImplements`
		abstract public function exports():object;

		// interface from Interactions namespace which will be consumers API with this module
		abstract public function exportsImplements():string;

		// arguments will be auto-wired
		public function entityBindings ():self {

			return $this;
		}

		public function getContainer():Container {
			
			return $this->container;
		}

		public function getConfigs():array {
			
			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IAuth::class => Auth::class,

				ITransphporm::class => Transphporm::class,

				HydratorContract::class => UserHydrator::class
			];
		}
	}
?>