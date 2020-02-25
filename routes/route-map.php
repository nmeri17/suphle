<?php

	use Tilwa\Route\Route;

	// this var is available in every file in your route path
	$registrar->register('', 'Home@index');
	
	$registrar->register('profile', 'Dashboard@profile', null, null, 'Authenticate');

	$registrar->register('404', 'Error@notFound');

	$registrar->register('401', 'Error@unauthorized');
	
	$registrar->register('signup', 'Authentication@showForm', 'auth/register/index'/*, null, null, 'Visitor'*/);
	
	$registrar->register('signup', 'Authentication@signup', false, Route::POST, null/*'Visitor'*/, Route::RELOAD);
?>