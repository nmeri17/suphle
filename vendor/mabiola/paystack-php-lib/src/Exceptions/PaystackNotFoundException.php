<?php
/**
 * Created by Malik Abiola.
 * Date: 12/02/2016
 * Time: 22:02
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackNotFoundException extends BaseException
{
    public function __construct($response, $code)
    {
        parent::__construct(isset($response->message) ? $response->message : 'Resource Not Found', $code);
    }
}
