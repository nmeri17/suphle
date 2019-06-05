<?php

namespace Electrum\Request\Method;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Address\IsMine as IsMineResponse;

/**
 * Check if address is in wallet. Return true if and only address is in wallet
 * @author Pascal Krason <p.krason@padr.io>
 */
class IsAddressMine extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'ismine';

    /**
     * @var string
     */
    private $address = '';

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return IsAddressMine
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param array $optional
     *
     * @return IsMineResponse
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, array_merge($optional, [
            'address' => $this->getAddress(),
        ]));

        return $this->hydrate(new IsMineResponse(), $data);
    }
}