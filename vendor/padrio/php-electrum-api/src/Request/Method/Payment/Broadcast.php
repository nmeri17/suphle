<?php

namespace Electrum\Request\Method\Payment;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Exception\ElectrumResponseException;

/**
 * Return a payment request.
 * @author Pascal Krason <p.krason@padr.io>
 */
class Broadcast extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'broadcast';

    /**
     * @var string
     * Serialized transaction (hexadecimal)
     */
    private $transaction = '';

    /**
     * @param array $optional
     *
     * @return PaymentRequestResponse|null
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, array_merge([
            'tx' => $this->getTransaction()
        ], $optional));

        return is_array($data) && isset($data[1]) ? $data[1] : $data;
    }

    /**
     * Get the value of Transaction
     *
     * @return string
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set the value of Transaction
     *
     * @param string transaction
     *
     * @return self
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

}
