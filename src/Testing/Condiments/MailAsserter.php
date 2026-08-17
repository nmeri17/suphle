<?php
namespace Suphle\Testing\Condiments;

use Suphle\Contracts\IO\MailClient;

use Suphle\Testing\Proxies\Extensions\MailDetailsCatcher;

trait MailAsserter {
    protected ?MailClient $mailAdapter = null;

    public function setUp(): void {

        parent::setUp();

        $this->catchMails();
    }

    protected function catchMails(): void
    {

        if (!is_null($this->mailAdapter)) return; // using this nonce so we can assert more than once in the same test without overwriting the instance

        $this->mailAdapter = $this->getContainer()

        ->getClass(MailDetailsCatcher::class);

        $this->massProvide([ // mass providing from the onset since we don't know yet what the active module is at this point this

            MailClient::class => $this->mailAdapter
        ]);
    }

    protected function assertSentMailTo(string $userMail): void {
        
        $this->assertSame($userMail, $this->mailAdapter->destination);
    }
}