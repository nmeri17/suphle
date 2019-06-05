<?php
/**
 * Created by Malik Abiola.
 * Date: 17/02/2016
 * Time: 17:21
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackNotFoundException;
use MAbiola\Paystack\Exceptions\PaystackUnauthorizedException;
use MAbiola\Paystack\Exceptions\PaystackValidationException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Helpers\Utils;
use MAbiola\Paystack\Repositories\TransactionResource;

class TransactionResourceTest extends BaseTestCase
{
    protected $paystackHttpClient;

    public function setUp()
    {
        parent::setUp();
        $this->paystackHttpClient = PaystackHttpClientFactory::make();
    }

    public function testInitializeSuccessful()
    {
        $transactionRequestBody = [
            'amount'    => $this->planData['amount'],
            'email'     => $this->getFakedCustomerData()['email'],
            'plan'      => '',
            'reference' => Utils::generateTransactionRef(),
        ];
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $initTransaction = $transactionResource->initialize($transactionRequestBody);

        $this->assertArrayHasKey('reference', $initTransaction);
        $this->assertArrayHasKey('access_code', $initTransaction);
        $this->assertArrayHasKey('authorization_url', $initTransaction);

        return $transactionRequestBody;
    }

    public function testInitializeReturnsError()
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $initTransaction = $transactionResource->initialize([]);
        $this->assertInstanceOf(PaystackValidationException::class, $initTransaction);
    }

    public function testGetAllTransactions()
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $transactions = $transactionResource->getAll();

        $this->assertGreaterThanOrEqual(1, count($transactions));
        $this->assertArrayHasKey('reference', $transactions[0]);
        $this->assertArrayHasKey('amount', $transactions[0]);
        $this->assertArrayHasKey('customer', $transactions[0]);
        $this->assertTrue(is_array($transactions[0]['customer']));

        return $transactions[0];
    }

    public function testGetAllTransactionReturnsError()
    {
        $transactionResource = new TransactionResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $transactions = $transactionResource->getAll();

        $this->assertInstanceOf(PaystackUnauthorizedException::class, $transactions);
    }

    /**
     * @depends testInitializeSuccessful
     *
     * @param $initData
     */
    public function testVerifyTransactionReturnsVerificationData($initData)
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $transaction = $transactionResource->verify($initData['reference']);

        $this->assertEquals($initData['amount'], $transaction['amount']);
        $this->assertEquals($initData['reference'], $transaction['reference']);
        $this->assertArrayHasKey('status', $transaction);
    }

    /**
     * @depends testInitializeSuccessful
     *
     * @param $initData
     */
    public function testVerifyTransactionReturnsError($initData)
    {
        $transactionResource = new TransactionResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $transaction = $transactionResource->verify($initData['reference']);

        $this->assertInstanceOf(PaystackUnauthorizedException::class, $transaction);
    }

    /**
     * @depends testGetAllTransactions
     *
     * @param $transactionData
     */
    public function testGetTransactionDetailsReturnsTransactionDetails($transactionData)
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $transactionDetails = $transactionResource->get($transactionData['id']);

        $this->assertArrayHasKey('reference', $transactionDetails);
        $this->assertArrayHasKey('amount', $transactionDetails);
        $this->assertArrayHasKey('customer', $transactionDetails);
        $this->assertTrue(is_array($transactionDetails['customer']));
    }

    /**
     * @depends testInitializeSuccessful
     *
     * @param $transactionData
     */
    public function testGetTransactionDetailsReturnsError($transactionData)
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $transactionDetails = $transactionResource->get($transactionData['reference']);

        $this->assertInstanceOf(PaystackNotFoundException::class, $transactionDetails);
    }

    public function testGetTransactionsTotals()
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $transactionTotals = $transactionResource->getTransactionTotals();

        $this->assertTrue(is_array($transactionTotals));
        $this->assertArrayHasKey('total_volume', $transactionTotals);
        $this->assertArrayHasKey('total_transactions', $transactionTotals);
        $this->assertArrayHasKey('pending_transfers', $transactionTotals);
    }

    public function testGetTransactionTotalsReturnsError()
    {
        $transactionResource = new TransactionResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $transactionTotals = $transactionResource->getTransactionTotals();

        $this->assertInstanceOf(PaystackUnauthorizedException::class, $transactionTotals);
        $this->assertStringStartsWith('Format is Authorization', $transactionTotals->getErrors());
    }

    public function testChargeAuthorizationReturnsError()
    {
        $transactionResource = new TransactionResource($this->paystackHttpClient);
        $chargeAuthorization = $transactionResource->chargeAuthorization([]);

        $this->assertInstanceOf(PaystackValidationException::class, $chargeAuthorization);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
