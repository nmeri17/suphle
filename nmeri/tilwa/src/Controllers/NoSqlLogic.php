<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{PermissibleService, BootsService};

	use Tilwa\App\Container;

	class NoSqlLogic implements PermissibleService, BootsService { // using [BootsService] instead of a service provider since it won't have a concrete. We will also wanna run multiple logic classes within one request
		final public function setup(Container $container):void {
			
			$container->whenType( self::class)->needsAny([

				"ormModel" => null
			]);
		}
	}
?>