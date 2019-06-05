<?php

namespace Electrum\Request\Method\Wallet;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Wallet\Transaction;
use Electrum\Response\Exception\BadResponseException;

/**
 * Wallet history. Returns the transaction history of your wallet.
 * @author Pascal Krason <p.krason@padr.io>
 */
class GetHistory extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'history';

    /**
     * @param array $optional
     *
     * @return HistoryResponse
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\BadResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, $optional);
        if (!is_array($data)) {
            $data = json_decode($data, true);
            if (isset($data['transactions'])) {
                $data = $data['transactions'];
            } else {
                throw new BadResponseException('Cannot get history');
            }
        }
        $transactions = [];
        foreach ($data as $transaction) {
            $transactions[] = $this->hydrate(new Transaction(), $transaction);
        }
        return $transactions;
    }
}
