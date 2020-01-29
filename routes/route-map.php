<?php

	// this var is available in every file in your route path
	$registrar->register('', 'Home@main', 'index');
	
	$registrar->register('profile', 'Dashboard@profile', null, null, null, 'Authenticate');

	$registrar->register('404', 'Error@notFound');

	$registrar->register('401', 'Error@unauthorized');
?>