<?php
namespace Suphle\Testing\Proxies\Extensions;

use Suphle\Adapters\Mailers\SymfonyMailer;

class MailDetailsCatcher extends SymfonyMailer {

	public string $destination, $text, $html;

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function setText(string $text): self
    {

        $this->text = $text;

        return $this;
    }

    public function setHtml(string $html): self
    {

        $this->html = $html;

        return $this;
    }

    public function fireMail(): void
    {
        //
    }
}