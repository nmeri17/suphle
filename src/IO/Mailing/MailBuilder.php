<?php

namespace Suphle\IO\Mailing;

abstract class MailBuilder
{
    protected $payload;

    public function setPayload($data): self
    {

        $this->payload = $data;

        return $this;
    }

    abstract public function sendMessage(): void;
}
