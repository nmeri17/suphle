<?php
/**
 * Created by Malik Abiola.
 * Date: 16/02/2016
 * Time: 11:46
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Helpers;

use MAbiola\Paystack\Abstractions\BaseTransaction;
use MAbiola\Paystack\Contracts\TransactionContract;
use MAbiola\Paystack\Models\Transaction as TransactionObject;

class Transaction extends BaseTransaction
{
    public static function make()
    {
        return new static();
    }

    /**
     * Verify Transaction.
     *
     * @param $transactionRef
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function verify($transactionRef)
    {
        $transactionData = $this->getTransactionResource()->verify($transactionRef);

        if ($transactionData instanceof \Exception) {
            throw $transactionData;
        }

        if ($transactionData['status'] == TransactionContract::TRANSACTION_STATUS_SUCCESS) {
            return [
                'authorization' => $transactionData['authorization'],
                'customer'      => $transactionData['customer'],
                'amount'        => $transactionData['amount'],
                'plan'          => $transactionData['plan'],
            ];
        }

        return false;
    }

    /**
     * Get transaction details.
     *
     * @param $transactionId
     *
     * @throws \Exception|mixed
     *
     * @return \MAbiola\Paystack\Models\Transaction
     */
    public function details($transactionId)
    {
        $transactionData = $this->getTransactionResource()->get($transactionId);

        if ($transactionData instanceof \Exception) {
            throw $transactionData;
        }

        return TransactionObject::make($transactionData);
    }

    /**
     * Get all transactions. per page.
     *
     * @param $page
     *
     * @throws \Exception|mixed
     *
     * @return array
     */
    public function allTransactions($page)
    {
        $transactions = [];
        $transactionData = $this->getTransactionResource()->getAll($page);

        if ($transactionData instanceof \Exception) {
            throw $transactionData;
        }

        foreach ($transactionData as $transaction) {
            $transactions[] = TransactionObject::make($transaction);
        }

        return $transactions;
    }

    /**
     * Get merchant transaction total.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function transactionsTotals()
    {
        $transactions = $this->getTransactionResource()->getTransactionTotals();
        if ($transactions instanceof \Exception) {
            throw $transactions;
        }

        return $transactions;
    }
}
