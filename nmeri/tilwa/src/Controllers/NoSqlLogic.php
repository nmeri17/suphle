<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{PermissibleService, BootsService, Orm};

	use Tilwa\App\Container;

	class NoSqlLogic implements PermissibleService, BootsService { // using [BootsService] instead of a service provider since it won't have a concrete. We will also wanna run multiple logic classes within one request

		private $factoryList = [];

		final public function setup(Container $container):void {
			
			$container->whenType( self::class)->needsAny([

				"ormModel" => null,

				Orm::class => null
			]);
		}

		protected function startConditional(string $interface):self {

			return $this;
		}

		protected function whenTrue(bool $condition, string $handlingClass, ...$arguments):self {

			$this->factoryList[$factoryName] = $useCases;

			return $this;
		}

		protected function finally(string $handlingClass, ...$arguments):self {
			
			$concrete = $this->factoryList[$factoryName](...$arguments);

			foreach ([self::class, $factoryName] as $parent)
				
				if (!$concrete instanceof $parent) return null;
			
			return $concrete;
		}

    public function getOrderPricing(array $addresses, float $orderWeight, bool $expressIsset):int {

        if ($this->isLagosReceiver($addresses["receiver"]->city->state_id))

            return $this->priceWithinLagos($addresses, $orderWeight, $expressIsset);

        return $this->priceOutsideLagos($addresses, $orderWeight, $expressIsset);
    }

    // can be rewritten as
    public function getOrderPricing(array $addresses, float $orderWeight, bool $expressIsset):int {

        return $this->startConditional (OrderCoster::class) // extends an interface with method [getValue]

        whenTrue($this->isLagosReceiver($addresses["receiver"]->city->state_id), PriceWithinLagos::class) // uses all the arguments

        ->whenTrue ($this->anotherCondition($orderWeight), PriceBetweenLagos::class, $orderWeight, $expressIsset )

        ->finally(PriceOutsideLagos::class, $expressIsset); // tests confirm those other conditions don't trigger this
    }
	}
?>