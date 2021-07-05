<?php
	namespace Tilwa\Tests\StronglyTyped\App;

	use Tilwa\App\Container;

	class ARequiresBCounter {

		private $b1, $container, $primitive;

		public function __construct (BCounter $b1, Container $container, string $primitive) {

			$this->b1 = $b1;

			$this->container = $container;

			$this->primitive = $primitive;
		}

		public function getConstructorB ():BCounter {

			return $this->b1;
		}

		public function getInternalB ():BCounter {

			return $this->container->getClass(BCounter::class);
		}

		public function getPrimitive ():string {

			return $this->primitive;
		}
	}
?>