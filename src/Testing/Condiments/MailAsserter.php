<?php
namespace Suphle\Testing\Condiments;

use Suphle\Contracts\IO\MailClient;

trait MailAsserter {
    private MailClient $mailAdapter = null;

    private function getMailAdapter():MailClient
    {
        return $this->mailAdapter ??= $this->getContainer()

        ->getClass(MailClient::class); // we already replaced this with a test suitable version in BaseModuleInteractor
    }

    protected function assertSentMailTo(string $userMail): void {
        
        $this->assertSame($userMail, $this->getMailAdapter()->destination);
    }
}