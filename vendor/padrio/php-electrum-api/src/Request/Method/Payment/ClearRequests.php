<?php

namespace Electrum\Request\Method\Payment;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;

/**
 * Remove all payment requests.
 * @author Pascal Krason <p.krason@padr.io>
 */
class ClearRequests extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'clearrequests';

    /**
     * @param array $optional
     *
     * @return boolean  Always true because electrum does not tell us anything else.
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $this->getClient()->execute($this->method, $optional);

        // Electrum just returns a NULL so we will never know if we succeeded
        return true;
    }
}