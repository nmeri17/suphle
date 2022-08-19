<?php
	namespace Suphle\Tests\Integration\IO;

	use Suphle\Contracts\IO\MailClient;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class MailerTest extends IsolatedComponentTest {

		use CommonBinds;

		public function test_smtp_can_connect () {

			$this->assertNotNull($this->container->getClass(MailClient::class));
		}
	}
?>