<?php

	namespace Controllers;

	use Nmeri\Tilwa\Controllers\GetController as TilwaGet;

	
	// Read operations on data not to be formatted -- json response via ajax call? string? API? Plug them here!
	class GetController extends TilwaGet {

		protected function getContentOptions ( string $rsxName ) {

		    $uniqCol = []; // 'id' here means we don't intend to `getContents` any rows here but don't want other table calls to complain when they can't find 'name'

			return ['primaryColumns' => $uniqCol];
		}
	 }

?>