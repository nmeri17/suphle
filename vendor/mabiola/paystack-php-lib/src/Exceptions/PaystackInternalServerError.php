<?php
/**
 * Created by Malik Abiola.
 * Date: 17/02/2016
 * Time: 17:56
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackInternalServerError extends BaseException
{
    public function __construct($response, $code)
    {
        $this->response = $response;
        parent::__construct(isset($response->message) ? $response->message : 'Gateway Timeout Error', $code);
    }
}
