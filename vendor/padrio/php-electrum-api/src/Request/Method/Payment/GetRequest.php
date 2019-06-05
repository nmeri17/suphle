<?php

namespace Electrum\Request\Method\Payment;

use Electrum\Request\AbstractMethod;
use Electrum\Request\Method\Traits\Param\Address;
use Electrum\Request\MethodInterface;
use Electrum\Response\Exception\ElectrumResponseException;
use Electrum\Response\Model\Payment\PaymentRequest as PaymentRequestResponse;
use Electrum\Response\Model\Payment\PaymentRequest;

/**
 * Return a payment request.
 * @author Pascal Krason <p.krason@padr.io>
 */
class GetRequest extends AbstractMethod implements MethodInterface
{

    /**
     * Import Address parameter through trait
     */
    use Address;

    /**
     * @var string
     */
    private $method = 'getrequest';

    /**
     * @param array $optional
     *
     * @return PaymentRequestResponse|null
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {

        try {

            // Yes, key instead of address. Because fuck consistency - Electrum Dev, 2016
            $data = $this->getClient()->execute($this->method, array_merge(['key' => $this->getAddress()], $optional));

        } catch(ElectrumResponseException $exception) {

            if($exception->getCode() == -32603) {
                return null;
            } else {
                throw $exception;
            }

        }

        return PaymentRequest::createFromArray($data);
    }
}