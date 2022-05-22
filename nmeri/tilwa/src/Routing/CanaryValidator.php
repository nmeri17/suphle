<?php
	namespace Tilwa\Routing;

	use Tilwa\Hydration\{Container, Structures\ObjectDetails};

	use Tilwa\Contracts\Routing\{CanaryGateway, RouteCollection};

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Exception\Explosives\Generic\InvalidImplementor;

	class CanaryValidator {

		private $allCanaries = [], $canaryInstances = [], $container,

		$objectMeta;

		public function __construct (Container $container, ObjectDetails $objectMeta) {
			
			$this->container = $container;

			$this->objectMeta = $objectMeta;
		}

		public function setCanaries (array $canaries):self {

			$this->allCanaries = $canaries;

			return $this;
		}

		public function collectionAuthStorage (AuthStorage $authStorage):self {

			foreach ($this->allCanaries as $canaryName)

				$this->container->whenType($canaryName)->needsArguments([

					AuthStorage::class => $authStorage
				]);

			return $this;
		}

		public function setValidCanaries ():self {

			$gatewayName = CanaryGateway::class;

			$collectionInterface = RouteCollection::class;

			array_walk($this->allCanaries, function ($canary) use ($gatewayName, $collectionInterface) {

				if ( !$this->objectMeta->implementsInterface(

					$canary, $gatewayName
				))

					throw new InvalidImplementor($gatewayName, $canary);

				$instance = $this->container->getClass($canary);

				$nextCollection = $instance->entryClass();

				if ( !$this->objectMeta->implementsInterface(

					$nextCollection, $collectionInterface
				))

					throw new InvalidImplementor($collectionInterface, $nextCollection);

				$this->canaryInstances[] = $instance;

			});

			return $this;
		}

		public function getCanaryInstances ():array {

			return $this->canaryInstances;
		}
	}
?>