<?php

	namespace Controllers;

	use Nmeri\Tilwa\Controllers\GetController as TilwaGet;

	
	class GetController extends TilwaGet {

		public function getContentOptions ( ) {

		    $uniqCol = ['user' => 'email']; // 'id' here means we don't intend to `getContents` any rows here but don't want other table calls to complain when they can't find 'name'

			return ['primaryColumns' => $uniqCol];
		}
	}

?>