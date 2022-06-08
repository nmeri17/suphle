<?php
	namespace Tilwa\Contracts\IO;

	interface MailClient {

		public function setDestination (string $destination):self;

		public function setSubject (string $subject):self;

		public function setText (string $text):self;

		public function setHtml (string $html):self;

		public function fireMail ():void;

		public function getNativeClient ();
	}
?>