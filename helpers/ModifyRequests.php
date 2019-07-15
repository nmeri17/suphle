<?php

	use \Get\TilwaGet;

	use \Post\TilwaPost;

/**
 * helper class for post controller invoked from the modify method
 */
class ModifyRequests {

	public $queriesMap = [
	];

	private $conn;

	function __construct(PDO $conn)
	{
		$this->conn = $conn;
	}
}