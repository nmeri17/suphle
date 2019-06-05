<?php

namespace Electrum\Response\Model\Payment;

use Electrum\Response\ResponseInterface;
use Electrum\Response\Hydrator\Payment\PaymentRequest as PaymentRequestHydrator;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class PaymentRequest implements ResponseInterface
{

    /**
     *
     */
    const STATUS_UNPAID = 'Pending';

    /**
     *
     */
    const STATUS_EXPIRED = 'Expired';

    /**
     * sent but not propagated
     */
    const STATUS_UNKNOWN = 'Unknown';

    /**
     *
     */
    const STATUS_PAID = 'Paid';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $status = '';

    /**
     * @var Amount|null
     */
    private $amount = null;

    /**
     * @var string
     */
    private $memo = '';

    /**
     * @var string
     */
    private $address = '';

    /**
     * @var string
     */
    private $uri = '';

    /**
     * @var int
     */
    private $expires = 0;

    /**
     * @var int
     */
    private $time = 0;

    /**
     * @var int
     */
    private $confirmations = null;

    /**
     * Factory method
     *
     * @param array $data
     *
     * @return PaymentRequest
     */
    public static function createFromArray(array $data)
    {
        /** @var PaymentRequest $paymentRequest */
        $paymentRequestResponse = null;

        $amountHydrator = new \Electrum\Response\Hydrator\Payment\Amount();
        $amount = $amountHydrator->hydrate($data, new Amount());

        $paymentRequestHydrator = new PaymentRequestHydrator();
        $paymentRequestResponse = $paymentRequestHydrator->hydrate(
            array_merge(
                $data, [
                'amount' => $amount
            ]),
            new self
        );

        return $paymentRequestResponse;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return PaymentRequest
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Amount|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount|null $amount
     *
     * @return PaymentRequest
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param string $memo
     *
     * @return PaymentRequest
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

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
     * @return PaymentRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return PaymentRequest
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     *
     * @return PaymentRequest
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     *
     * @return PaymentRequest
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return int
     */
    public function getConfirmations()
    {
        return $this->confirmations;
    }

    /**
     * @param int $confirmations
     *
     * @return PaymentRequest
     */
    public function setConfirmations($confirmations)
    {
        $this->confirmations = $confirmations;

        return $this;
    }

}