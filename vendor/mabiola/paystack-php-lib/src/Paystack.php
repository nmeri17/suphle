<?php
/**
 * Created by Malik Abiola.
 * Date: 04/02/2016
 * Time: 23:21
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack;

use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Helpers\Transaction as TransactionHelper;
use MAbiola\Paystack\Models\Customer;
use MAbiola\Paystack\Models\OneTimeTransaction;
use MAbiola\Paystack\Models\Plan;
use MAbiola\Paystack\Models\ReturningTransaction;
use MAbiola\Paystack\Repositories\CustomerResource;
use MAbiola\Paystack\Repositories\PlanResource;
use MAbiola\Paystack\Repositories\TransactionResource;

class Paystack
{
    private $paystackHttpClient;
    private $customerModel;
    private $planModel;
    private $customerResource;
    private $transactionResource;
    private $planResource;
    private $transactionHelper;

    /**
     * Paystack constructor.
     *
     * @param $key
     */
    private function __construct($key)
    {
        $this->paystackHttpClient = $this->makePaystackHttpClient($key);

        $this->customerResource = new CustomerResource($this->paystackHttpClient);
        $this->customerModel = new Customer($this->customerResource);

        $this->transactionResource = new TransactionResource($this->paystackHttpClient);

        $this->planResource = new PlanResource($this->paystackHttpClient);
        $this->planModel = new Plan($this->planResource);

        $this->transactionHelper = TransactionHelper::make();
        $this->transactionHelper->setTransactionResource($this->transactionResource);
    }

    /**
     * Make a new Paystack library object.
     *
     * @param null $key
     *
     * @return Paystack
     */
    public static function make($key = null)
    {
        return new self($key);
    }

    /**
     * Get customer by ID.
     *
     * @param $customerId
     *
     * @throws \Exception|mixed
     *
     * @return Customer | \Exception
     */
    public function getCustomer($customerId)
    {
        return $this->getCustomerModel()->getCustomer($customerId);
    }

    /**
     * Get all customers.
     *
     * @param string $page
     *
     * @throws \Exception|mixed
     *
     * @return \Exception|mixed
     */
    public function getCustomers($page = '')
    {
        $customerObjects = [];
        $customers = $this->getCustomerResource()->getAll($page);

        if ($customers instanceof \Exception) {
            throw $customers;
        }

        foreach ($customers as $customer) {
            $customerObject = new Customer($this->getCustomerResource());
            $customerObjects[] = $customerObject->_setAttributes($customer);
        }

        return $customerObjects;
    }

    /**
     * Create new customer.
     *
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $phone
     *
     * @throws \Exception|mixed
     * @throws null
     *
     * @return mixed
     */
    public function createCustomer($first_name, $last_name, $email, $phone)
    {
        return $this->getCustomerModel()->make($first_name, $last_name, $email, $phone)->save();
    }

    /**
     * Update customer by customer id/code.
     *
     * @param $customerId
     * @param array $updateData
     *
     * @throws \Exception|mixed
     * @throws null
     *
     * @return mixed
     */
    public function updateCustomerData($customerId, $updateData)
    {
        return $this->getCustomerModel()->getCustomer($customerId)
            ->setUpdateData($updateData)
            ->save();
    }

    /**
     * Delete customer by Id/Code.
     *
     * @param $customerId
     *
     * @return mixed
     */
    public function deleteCustomer($customerId)
    {
        return $this->getCustomerModel()->getCustomer($customerId)->delete();
    }

    /**
     * Get plan by plan id/code.
     *
     * @param $planCode
     *
     * @throws \Exception|mixed
     *
     * @return mixed
     */
    public function getPlan($planCode)
    {
        return $this->getPlanModel()->getPlan($planCode);
    }

    /**
     * Get all plans.
     *
     * @param string $page
     *
     * @throws \Exception|mixed
     *
     * @return \Exception|mixed
     */
    public function getPlans($page = '')
    {
        $planObjects = [];
        $plans = $this->getPlanResource()->getAll($page);

        if ($plans instanceof \Exception) {
            throw $plans;
        }

        foreach ($plans as $plan) {
            $planObject = new Plan($this->getPlanResource());
            $planObjects[] = $planObject->_setAttributes($plan);
        }

        return $planObjects;
    }

    /**
     * Create new plan.
     *
     * @param $name
     * @param $description
     * @param $amount
     * @param $currency
     *
     * @throws \Exception|mixed
     * @throws null
     *
     * @return mixed
     */
    public function createPlan($name, $description, $amount, $currency)
    {
        return $this->getPlanModel()->make($name, $description, $amount, $currency)->save();
    }

    /**
     * Update plan.
     *
     * @param $planCode
     * @param $updateData
     *
     * @throws \Exception|mixed
     * @throws null
     *
     * @return mixed
     */
    public function updatePlan($planCode, $updateData)
    {
        return $this->getPlanModel()->getPlan($planCode)
            ->setUpdateData($updateData)
            ->save();
    }

    /**
     * delete plans.
     *
     * @param $planCode
     *
     * @return $this
     */
    public function deletePlan($planCode)
    {
        return $this->getPlanModel()->getPlan($planCode)->delete();
    }

    /**
     * Init a one time transaction to get payment page url.
     *
     * @param $amount
     * @param $email
     * @param string $plan
     *
     * @throws \Exception|mixed|Exceptions\PaystackInvalidTransactionException
     *
     * @return \Exception|mixed|Exceptions\PaystackInvalidTransactionException
     */
    public function startOneTimeTransaction($amount, $email, $plan = '')
    {
        $oneTimeTransaction = OneTimeTransaction::make(
            $amount,
            $email,
            $plan instanceof Plan ? $plan->get('plan_code') : $plan
        );
        $oneTimeTransaction->setTransactionResource($this->getTransactionResource());
        $transaction = $oneTimeTransaction->initialize();

        if ($transaction instanceof \Exception) {
            throw $transaction;
        }

        return $transaction;
    }

    /**
     * Charge a returning customer.
     *
     * @param $authorization
     * @param $amount
     * @param $email
     * @param string $plan
     *
     * @throws \Exception|mixed|Exceptions\PaystackInvalidTransactionException
     *
     * @return \Exception|mixed|Exceptions\PaystackInvalidTransactionException
     */
    public function chargeReturningTransaction($authorization, $amount, $email, $plan = '')
    {
        $returningTransaction = ReturningTransaction::make(
            $authorization,
            $amount,
            $email,
            $plan instanceof Plan ? $plan->get('plan_code') : $plan
        );
        $returningTransaction->setTransactionResource($this->getTransactionResource());

        $transaction = $returningTransaction->charge();

        if ($transaction instanceof \Exception) {
            throw $transaction;
        }

        return $transaction;
    }

    /**
     * Verify transaction.
     *
     * @param $transactionRef
     *
     * @return array|bool
     */
    public function verifyTransaction($transactionRef)
    {
        return $this->getTransactionHelper()->verify($transactionRef);
    }

    /**
     * Get transaction details.
     *
     * @param $transactionId
     *
     * @throws \Exception|mixed
     *
     * @return static
     */
    public function transactionDetails($transactionId)
    {
        return $this->getTransactionHelper()->details($transactionId);
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
    public function allTransactions($page = '')
    {
        return $this->getTransactionHelper()->allTransactions($page);
    }

    /**
     * Get successful transactions volume or totals.
     *
     * @throws
     *
     * @return mixed
     */
    public function transactionsTotals()
    {
        return $this->getTransactionHelper()->transactionsTotals();
    }

    /**
     * @return TransactionResource
     */
    private function getTransactionResource()
    {
        return $this->transactionResource;
    }

    /**
     * @param TransactionResource $transactionResource
     */
    public function setTransactionResource($transactionResource)
    {
        $this->transactionResource = $transactionResource;
    }

    /**
     * @return CustomerResource
     */
    private function getCustomerResource()
    {
        return $this->customerResource;
    }

    /**
     * @param CustomerResource $customerResource
     */
    public function setCustomerResource($customerResource)
    {
        $this->customerResource = $customerResource;
    }

    /**
     * @return PlanResource
     */
    private function getPlanResource()
    {
        return $this->planResource;
    }

    /**
     * @param PlanResource $planResource
     */
    public function setPlanResource($planResource)
    {
        $this->planResource = $planResource;
    }

    /**
     * @return Customer
     */
    private function getCustomerModel()
    {
        return $this->customerModel;
    }

    /**
     * @param Customer $customerModel
     */
    public function setCustomerModel($customerModel)
    {
        $this->customerModel = $customerModel;
    }

    /**
     * @return Plan
     */
    private function getPlanModel()
    {
        return $this->planModel;
    }

    /**
     * @param Plan $planModel
     */
    public function setPlanModel($planModel)
    {
        $this->planModel = $planModel;
    }

    /**
     * @return TransactionHelper
     */
    private function getTransactionHelper()
    {
        return $this->transactionHelper;
    }

    /**
     * @param TransactionHelper $transactionHelper
     */
    public function setTransactionHelper($transactionHelper)
    {
        $this->transactionHelper = $transactionHelper;
    }

    /**
     * Make a HTTP Client for making requests.
     *
     * @param $key
     *
     * @return \GuzzleHttp\Client
     */
    private function makePaystackHttpClient($key)
    {
        return is_null($key) ?
            PaystackHttpClientFactory::make() :
            PaystackHttpClientFactory::make(
                [
                    'headers'   => [
                        'Authorization' => 'Bearer '.$key,
                        'Content-Type'  => 'application/json',
                    ],
                ]
            );
    }

    /**
     * Get the created HTTP Client.
     *
     * @return \GuzzleHttp\Client
     */
    public function getPaystackHttpClient()
    {
        return $this->paystackHttpClient;
    }
}
