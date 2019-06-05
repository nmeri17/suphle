<?php
/**
 * Created by Malik Abiola.
 * Date: 14/02/2016
 * Time: 20:15
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackUnsupportedOperationException extends BaseException
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}
