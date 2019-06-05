<?php

namespace Electrum\Request\Method\Payment;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Payment\PaymentRequest as PaymentRequestResponse;
use Electrum\Response\Model\Payment\PaymentRequest;

/**
 * List the payment requests you made.
 * @author Pascal Krason <p.krason@padr.io>
 */
class GetRequests extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'listrequests';

    /**
     * @param array $optional
     *
     * @return PaymentRequestResponse[]
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, $optional);

        $requests = [];
        foreach($data as $request) {
            $requests[] = PaymentRequest::createFromArray($request);
        }

        return $requests;
    }
}