<?php
/**
 * Created by Malik Abiola.
 * Date: 10/02/2016
 * Time: 16:35
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackInvalidTransactionException extends BaseException
{
    /**
     * PaystackInvalidTransactionException constructor.
     *
     * @param $response
     * @param $code
     */
    public function __construct($response, $code = 0)
    {
        parent::__construct($response->message, $code);
    }
}
