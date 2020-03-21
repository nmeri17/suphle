<?php

	use Tilwa\Route\Route;

	// this var is available in every file in your route path
	$registrar->register('', 'Home@index', 'index');
	
	$registrar->register('profile', 'Dashboard@profile', null, null, 'Authenticate');

	$registrar->register('404', 'Errors@notFound');

	$registrar->register('401', 'Errors@unauthorized');
	
	$registrar->register('signup', 'Authentication@showForm', 'auth/register/index'/*, null, null, 'Visitor'*/);
	
	$registrar->register('signup', 'Authentication@signup', false, Route::POST, null/*'Visitor'*/, function () { // I _think_ closures are unserializable and can't be stored as prev request

		return '/';
	});
	
	$registrar->register('login', 'Authentication@showLogin', 'auth/login'/*, null, null, 'Visitor'*/);
	
	$registrar->register('login', 'Authentication@signin', false, Route::POST, null/*'Visitor'*/, function ($payload, $forwardPop) {

		return $forwardPop('/profile');
	});
?>