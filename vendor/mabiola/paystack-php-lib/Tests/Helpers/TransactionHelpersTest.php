<?php
/**
 * Created by Malik Abiola.
 * Date: 16/02/2016
 * Time: 12:23
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackNotFoundException;
use MAbiola\Paystack\Exceptions\PaystackUnauthorizedException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Helpers\Transaction;
use MAbiola\Paystack\Repositories\TransactionResource;

class TransactionHelpersTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->transactionResource = new TransactionResource(PaystackHttpClientFactory::make());
    }

    public function testRetrieveTransactionDetailsReturnsTransactionDetails()
    {
        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('get')
            ->once()
            ->andReturn($this->transactionDetailsResponseData);

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $transaction = $transactionHelper->details('9663');

        $this->assertInstanceOf(\MAbiola\Paystack\Models\Transaction::class, $transaction);
        $this->assertEquals($this->transactionDetailsResponseData['reference'], $transaction->get('reference'));

        $this->assertTrue(is_array($transaction->_toArray()));
    }

    public function testRetrieveTransactionDetailsReturnsException()
    {
        $invalidResponse = new \stdClass();
        $invalidResponse->message = 'Transaction Not Found';

        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('get')
            ->once()
            ->andReturn(new PaystackNotFoundException($invalidResponse, 404));

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $this->setExpectedException(PaystackNotFoundException::class);
        $transactionHelper->details('9663');
    }

    public function testGetAllTransactionsReturnsAllTransactions()
    {
        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('getAll')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->allTransactionsResponseData);

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $allTransactions = $transactionHelper->allTransactions(1);

        $this->assertTrue(is_array($allTransactions));
        $this->assertCount(4, $allTransactions);
        $this->assertInstanceOf(\MAbiola\Paystack\Models\Transaction::class, $allTransactions[0]);
        $this->assertArrayHasKey('customer', $allTransactions[0]->get(['customer']));
    }

    public function testGetAllTransactionsReturnsException()
    {
        $invalidResponse = new \stdClass();
        $invalidResponse->message = 'Transaction Not Found';

        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('getAll')
            ->withAnyArgs()
            ->once()
            ->andReturn(new PaystackNotFoundException($invalidResponse, 404));

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $this->setExpectedException(PaystackNotFoundException::class);
        $transactionHelper->allTransactions(1);
    }

    public function testGetTransactionTotalsReturnsTransactionTotals()
    {
        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('getTransactionTotals')
            ->once()
            ->andReturn($this->transactionTotalsResponseData);

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $transactionTotals = $transactionHelper->transactionsTotals();

        $this->assertTrue(is_array($transactionTotals));
        $this->assertArrayHasKey('total_volume', $transactionTotals);
        $this->assertArrayHasKey('total_transactions', $transactionTotals);
        $this->assertArrayHasKey('pending_transfers', $transactionTotals);
        $this->assertEquals($this->transactionTotalsResponseData['total_transactions'], $transactionTotals['total_transactions']);
    }

    public function testGetTransactionTotalsReturnsException()
    {
        $invalidResponse = new \stdClass();
        $invalidResponse->message = 'Authorization Not Found';

        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('getTransactionTotals')
            ->once()
            ->andReturn(new PaystackUnauthorizedException($invalidResponse, 401));

        $this->setExpectedException(PaystackUnauthorizedException::class);
        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $transactionHelper->transactionsTotals();
    }

    public function testVerifyTransactionReturnsTransaction()
    {
        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('verify')
            ->once()
            ->andReturn($this->verifyTransactionResponseData);

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $verify = $transactionHelper->verify($this->initOneTimeTransactionResourceResponseData['reference']);

        $this->assertTrue(is_array($verify));
        $this->assertArrayHasKey('customer', $verify);
        $this->assertArrayHasKey('amount', $verify);
        $this->assertArrayHasKey('plan', $verify);
    }

    public function testVerifyTransactionReturnsFalse()
    {
        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('verify')
            ->once()
            ->andReturn(['status' => 'unsuccessful']);

        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $verify = $transactionHelper->verify($this->initOneTimeTransactionResourceResponseData['reference']);

        $this->assertFalse($verify);
    }

    public function testVerifyTransactionThrowsException()
    {
        $invalidResponse = new \stdClass();
        $invalidResponse->message = 'Authorization Not Found';

        $mockTransactionResource = \Mockery::mock($this->transactionResource)->makePartial();
        $mockTransactionResource->shouldReceive('verify')
            ->once()
            ->andReturn(new PaystackUnauthorizedException($invalidResponse, 401));

        $this->setExpectedException(PaystackUnauthorizedException::class);
        $transactionHelper = Transaction::make();
        $transactionHelper->setTransactionResource($mockTransactionResource);

        $transactionHelper->verify($this->initOneTimeTransactionResourceResponseData['reference']);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
