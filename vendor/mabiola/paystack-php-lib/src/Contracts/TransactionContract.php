<?php
/**
 * Created by Malik Abiola.
 * Date: 12/02/2016
 * Time: 21:29
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Contracts;

interface TransactionContract
{
    const TRANSACTION_STATUS_SUCCESS = 'success';

    public function _requestPayload();
}
