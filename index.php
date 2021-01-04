<?php

	require '../autoload.php';

	use Modules\{Main, Cart, Category, Product, Auth};

	$entrance = new Tilwa\App\FrontController([
		new Main, new Cart, new Category, new Product, new Auth
	]); // so, each of these guys can dictate what gets wired in where

	$preRespo = $entrance->responseManager->getResponse();

	foreach ($entrance->postResEventList as $handler)

		$preRespo = $handler($preRespo);

	echo $preRespo;
?>