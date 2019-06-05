<?php

namespace Electrum\Request\Method\Address;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Address\Unspent as UnspentResponse;

/**
 * Returns the UTXO list of any address.
 * Note: This is a walletless server query, results are not checked by SPV.
 * @author Pascal Krason <p.krason@padr.io>
 */
class GetAddressUnspent extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'getaddressunspent';

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
     * @return GetAddressUnspent
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param array $optional
     *
     * @return UnspentResponse
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, array_merge($optional, [
            'address' => $this->getAddress(),
        ]));

        return $this->hydrate(new UnspentResponse(), $data);
    }
}