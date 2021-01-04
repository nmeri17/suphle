<?php

	require '../autoload.php';

	use Tilwa\App\ModuleToRoute;

	use Modules\{Main, Cart, Category, Product, Auth, Sellers, Errors};

	use Interactions\{CategoryExports, ProductExports};

	$Category = new Category;

	$Product = new Product;

	$Sellers = (new Sellers)->setDependsOn([
		CategoryExports::class => $Category, // note: this doesn't match Category itself, but its `exports`
		
		ProductExports::class => $Product
	]);

	$modules = [
		new Main, new Cart, $Category, $Product, new Auth, $Sellers,
		new Errors // this must be the last for it to catch unmatched routes. the route file should have a _notFound method that catches anything thrown at it
	];

	echo (new ModuleToRoute)->findContext($modules)->trigger();
?>