<?php
	namespace Suphle\Routing;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Contracts\Routing\{CanaryGateway, RouteCollection};

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	class CanaryValidator {

		private array $allCanaries = [];
  private $canaryInstances = [];

		public function __construct(private readonly Container $container, private readonly ObjectDetails $objectMeta)
  {
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