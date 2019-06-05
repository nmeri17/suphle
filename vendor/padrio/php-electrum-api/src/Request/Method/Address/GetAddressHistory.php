<?php

namespace Electrum\Request\Method\Address;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Address\History as HistoryResponse;

/**
 * Return the transaction history of any address.
 * Note: This is a walletless server query, results are not checked by SPV.
 * @author Pascal Krason <p.krason@padr.io>
 */
class GetAddressHistory extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'getaddresshistory';

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
     * @return GetAddressHistory
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param array $optional
     *
     * @return HistoryResponse
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, array_merge($optional, [
            'address' => $this->getAddress(),
        ]));

        return $this->hydrate(new HistoryResponse(), $data);
    }
}