<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	class ARequiresBCounter {

		private $b1, $container, $primitive, $cInterface;

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

		public function receiveBCounter (BCounter $injected):void {

			$this->b1 = $injected;
		}

		public function receiveProvidedInterface (CInterface $injected):void {

			$this->cInterface = $injected;
		}

		public function getCInterface():CInterface {

			return $this->cInterface;
		}
	}
?>