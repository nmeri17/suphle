<?php

	require '../autoload.php';

	use Tilwa\App\ModuleAssembly;

	use Modules\{Main, Cart, Category, Product, Auth, Sellers, Errors}; // correct this import

	use Interactions\{CategoryExports, ProductExports};

	class MyApp extends ModuleAssembly {
		
		function getModules():array {

			$Category = new Category;

			$Product = new Product;

			$Sellers = (new Sellers)->setDependsOn([
				CategoryExports::class => $Category, // note: this doesn't match Category itself, but its `exports`
				
				ProductExports::class => $Product
			]);

			$Cart = (new Cart)->setDependsOn([
				ProductExports::class => $Product
			]);

			return [
				new Main, $Cart, $Category, $Product, new Auth, $Sellers
			];
		}
	}

	(new MyApp)->orchestrate();
?>