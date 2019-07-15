<?php

use \Get\TilwaGet;

use \Post\TilwaPost;

use \Monolog\Logger;

use \Monolog\Handler\NativeMailerHandler;

use \Monolog\Formatter\HtmlFormatter;

/**
 * helper class containing methods called from the `create` method on the post controller
 */
class CreateRequests {
	
	private $conn;

	private $payload;

	function __construct(PDO $conn, array $payload ) {

		$this->conn = $conn;

		$this->payload = $payload;
	}

	public function broadcast () {

		$allUsers = $this->conn->prepare('SELECT email FROM users');	

		$allUsers->execute();

		$allUsers = $allUsers->fetchAll(PDO::FETCH_ASSOC);

		$receipients = implode(',', array_column($allUsers, 'email'));

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
		$headers .= 'From: Tilwa Admin <no-reply@tilwa.com>' . "\r\n";

		mail($receipients, $this->payload['subject'], $this->payload['content'], $headers);

	}
}

?>