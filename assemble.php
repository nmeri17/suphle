<?php

	require '../autoload.php';

	use Tilwa\App\{ModuleToRoute, EnvironmentDefaults};

	use Tilwa\Events\ModuleLevelEvents;

	use Modules\{Main, Cart, Category, Product, Auth, Sellers, Errors}; // correct this import

	use Interactions\{CategoryExports, ProductExports};

	$Category = new Category;

	$Product = new Product;

	$Sellers = (new Sellers)->setDependsOn([
		CategoryExports::class => $Category, // note: this doesn't match Category itself, but its `exports`
		
		ProductExports::class => $Product
	]);

	$Cart = (new Cart)->setDependsOn([
		ProductExports::class => $Product
	]);

	$modules = [
		new Main, $Cart, $Category, $Product, new Auth, $Sellers,
		new Errors // this must be the last for it to catch unmatched routes. the route file should have a _notFound method that catches anything thrown at it
	];

	$context = (new ModuleToRoute)->findContext($modules);

	new EnvironmentDefaults;

	(new ModuleLevelEvents)->bootReactiveLogger($modules);

	echo $context->trigger();
?>