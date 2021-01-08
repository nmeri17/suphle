<?php

	namespace AppRoutes;

	use Tilwa\Routing\{Route, RouteCollection};

	class MainRoutes extends RouteCollection {
		
		public function report() {
			
			return $this->_prefixFor(ReportsRoutes::class);
		}
	}

	$registrar->register('', 'Home@index', 'index');
	
	$registrar->register('profile', 'Dashboard@profile', null, null, 'Authenticate');

	$registrar->register('404', 'Errors@notFound');

	$registrar->register('401', 'Errors@unauthorized');
	
	$registrar->register('signup', 'Authentication@showRegisterForm', 'auth/register/index', null, 'NoAccount');
	
	$registrar->register('signup', 'Authentication@signup', false, Route::POST, 'NoAccount', function () {

		return '/';
	});
	
	$registrar->register('login', 'Authentication@showLoginForm', 'auth/login', null, 'NoAccount');
	
	$registrar->register('login', 'Authentication@signin', false, Route::POST, 'NoAccount', function ($payload, $forwardPop) {

		return $forwardPop('/profile');
	});
	
	$registrar->register('logout', 'Authentication@signout', false, Route::POST, null, function () {

		return '/';
	});
?>