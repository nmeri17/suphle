<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{PermissibleService, BootsService, Orm};

	use Tilwa\App\Container;

	class NoSqlLogic implements PermissibleService, BootsService { // using [BootsService] instead of a service provider since it won't have a concrete. We will also wanna run multiple logic classes within one request
		final public function setup(Container $container):void {
			
			$container->whenType( self::class)->needsAny([

				"ormModel" => null,

				Orm::class => null
			]);

			$this->registerFactories();
		}

		public function registerFactories() {
			// to be overridden
		}

		/**
		* @desc calls to this goes inside [registerFactories]
		* @param {useCases} class with an [__invoke] method
		*/
		protected function factoryFor(string $interface, string $useCases):self {

			if (is_null($this->factoryList))

				$this->factoryList = [];

			$this->factoryList[$interface] = $useCases;

			return $this;
		}
	}
?>