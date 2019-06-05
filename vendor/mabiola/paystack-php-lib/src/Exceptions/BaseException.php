<?php
/**
 * Created by Malik Abiola.
 * Date: 08/02/2016
 * Time: 22:21
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

abstract class BaseException extends \Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function getErrors()
    {
        return $this->getMessage();
    }
}
