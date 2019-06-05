<?php

namespace Electrum\Request\Method\Traits\Param;

use Electrum\Response\Model\Payment\PaymentRequest;
use InvalidArgumentException;

/**
 * Trait Address
 * @package Electrum\Request\Method\Traits\Param
 */
trait Address
{
    /**
     * @var string
     */
    private $address = null;

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
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param PaymentRequest $request
     *
     * @return $this
     */
    public function setPaymentRequest(PaymentRequest $request)
    {
        if(empty($request->getAddress())) {
            throw new InvalidArgumentException(sprintf(
                '$request does not contain valid address, %s given',
                $request->getAddress()
            ));
        }

        $this->address = $request->getAddress();

        return $this;
    }
}