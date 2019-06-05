<?php
/**
 * Created by Malik Abiola.
 * Date: 10/02/2016
 * Time: 16:10
 * IDE: PhpStorm
 * Create one time transactions.
 */
namespace MAbiola\Paystack\Models;

use MAbiola\Paystack\Abstractions\BaseTransaction;
use MAbiola\Paystack\Contracts\TransactionContract;
use MAbiola\Paystack\Exceptions\PaystackInvalidTransactionException;

class OneTimeTransaction extends BaseTransaction implements TransactionContract
{
    //    protected $transactionResource;

    private $transactionRef;
    private $amount;
    private $email;
    private $plan;

    /**
     * OneTimeTransaction constructor.
     *
     * @param $transactionRef
     * @param $amount
     * @param $email
     * @param $plan
     */
    protected function __construct($transactionRef, $amount, $email, $plan)
    {
        $this->transactionRef = $transactionRef;
        $this->amount = $amount;
        $this->email = $email;
        $this->plan = $plan;

//        $this->transactionResource = $transactionResource;
    }

    /**
     * Make a new one time transaction object.
     *
     * @param $amount
     * @param $email
     * @param $plan
     *
     * @return static
     */
    public static function make($amount, $email, $plan)
    {
        return new static(self::generateTransactionRef(), $amount, $email, $plan);
    }

    /**
     * Initialize one time transaction to get payment url.
     *
     * @return \Exception|mixed|PaystackInvalidTransactionException
     */
    public function initialize()
    {
        return !is_null($this->transactionRef) ?
           $this->getTransactionResource()->initialize($this->_requestPayload()) :
            new PaystackInvalidTransactionException(
                json_decode(
                    json_encode(
                        [
                            'message' => 'Transaction Reference Not Generated.',
                        ]
                    ),
                    false
                )
            );
    }

    /**
     * Get transaction request body.
     *
     * @return string
     */
    public function _requestPayload()
    {
        $payload = [
            'amount'    => $this->amount,
            'reference' => $this->transactionRef,
            'email'     => $this->email,
        ];

        if (!empty($this->plan)) {
            $payload['plan'] = $this->plan;
        }

        return $this->toJson($payload);
    }
}
