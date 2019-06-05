<?php

namespace Electrum\Request\Method\Payment;

use Electrum\Request\AbstractMethod;
use Electrum\Request\Method\Traits\Param\Address;
use Electrum\Request\MethodInterface;

/**
 * Remove a payment request.
 * @author Pascal Krason <p.krason@padr.io>
 */
class RemoveRequest extends AbstractMethod implements MethodInterface
{

    use Address;

    /**
     * @var string
     */
    private $method = 'rmrequest';


    /**
     * @param array $optional
     *
     * @return boolean
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        return $this->getClient()->execute($this->method,
            array_merge(
                [
                    'address' => $this->getAddress()
                ],
                $optional
            )
        );
    }
}