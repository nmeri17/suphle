<?php

namespace Electrum\Response\Traits;

/**
 * Trait Transactions
 * @package Electrum\Response\Traits
 */
trait Transactions
{
    /**
     * @var array
     */
    protected $transactions = [];

    /**
     * @return array
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param array $transactions
     *
     * @return Transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;

        return $this;
    }
}