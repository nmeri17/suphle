<?php
/**
 * Created by Malik Abiola.
 * Date: 13/02/2016
 * Time: 08:30
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Abstractions;

use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Helpers\Utils;
use MAbiola\Paystack\Repositories\TransactionResource;

abstract class BaseTransaction
{
    use Utils;

    protected $transactionResource;

    /**
     * Get set transaction resource.
     *
     * @return mixed
     */
    public function getTransactionResource()
    {
        return $this->transactionResource ?: new TransactionResource(PaystackHttpClientFactory::make());
    }

    /**
     * Set transaction resource.
     *
     * @param mixed $transactionResource
     */
    public function setTransactionResource(TransactionResource $transactionResource)
    {
        $this->transactionResource = $transactionResource;
    }
}
