<?php
/**
 * Created by Malik Abiola.
 * Date: 08/02/2016
 * Time: 22:21
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackUnauthorizedException extends BaseException
{
    public function __construct($response, $code)
    {
        parent::__construct($response->message, $code);
    }
}
