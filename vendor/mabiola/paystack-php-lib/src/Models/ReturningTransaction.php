<?php
/**
 * Created by Malik Abiola.
 * Date: 10/02/2016
 * Time: 16:20
 * IDE: PhpStorm
 * Create returning transaction.
 */
namespace MAbiola\Paystack\Models;

use MAbiola\Paystack\Abstractions\BaseTransaction;
use MAbiola\Paystack\Contracts\TransactionContract;
use MAbiola\Paystack\Exceptions\PaystackInvalidTransactionException;
use MAbiola\Paystack\Helpers\Utils;

class ReturningTransaction extends BaseTransaction implements TransactionContract
{
    use Utils;

    private $transactionRef;
    private $authorization;
    private $amount;
    private $email;
    private $plan;

    /**
     * ReturningTransaction constructor.
     *
     * @param $transactionRef
     * @param $authorization
     * @param $amount
     * @param $email
     * @param $plan
     */
    protected function __construct(
        $transactionRef,
        $authorization,
        $amount,
        $email,
        $plan
    ) {
        $this->transactionRef = $transactionRef;
        $this->authorization = $authorization;
        $this->amount = $amount;
        $this->email = $email;
        $this->plan = $plan;
    }

    /**
     * Create a new returning transaction object.
     *
     * @param $authorization
     * @param $amount
     * @param $email
     * @param $plan
     *
     * @return static
     */
    public static function make($authorization, $amount, $email, $plan)
    {
        return new static(
            self::generateTransactionRef(),
            $authorization,
            $amount,
            $email,
            $plan
        );
    }

    /**
     * Charge returning transaction.
     *
     * @return \Exception|mixed|PaystackInvalidTransactionException
     */
    public function charge()
    {
        return !is_null($this->transactionRef) ?
            $this->getTransactionResource()->chargeAuthorization($this->_requestPayload()) :
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
     * Get returning transaction request body.
     *
     * @return string
     */
    public function _requestPayload()
    {
        $payload = [
            'authorization_code'    => $this->authorization,
            'amount'                => $this->amount,
            'reference'             => $this->transactionRef,
            'email'                 => $this->email,
        ];

        if (!empty($this->plan)) {
            $payload['plan'] = $this->plan;
        }

        return $this->toJson($payload);
    }
}
