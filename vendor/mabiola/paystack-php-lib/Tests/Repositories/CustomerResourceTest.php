<?php

namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackNotFoundException;
use MAbiola\Paystack\Exceptions\PaystackUnauthorizedException;
use MAbiola\Paystack\Exceptions\PaystackValidationException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Repositories\CustomerResource;

/**
 * Created by Malik Abiola.
 * Date: 17/02/2016
 * Time: 13:36
 * IDE: PhpStorm.
 */
class CustomerResourceTest extends BaseTestCase
{
    protected $paystackHttpClient;

    public function setUp()
    {
        parent::setUp();
        $this->paystackHttpClient = PaystackHttpClientFactory::make();
    }

    public function testCreateUserSuccessful()
    {
        $this->customerData = $this->getFakedCustomerData();

        $customerResource = new CustomerResource($this->paystackHttpClient);
        $createdCustomer = $customerResource->save($this->customerData);

        $this->assertTrue(is_array($createdCustomer));
        $this->assertEquals($this->customerData['first_name'], $createdCustomer['first_name']);
        $this->assertEquals($this->customerData['last_name'], $createdCustomer['last_name']);
        $this->assertEquals($this->customerData['email'], $createdCustomer['email']);
        $this->assertEquals($this->customerData['phone'], $createdCustomer['phone']);

        return $createdCustomer;
    }

    public function testCreateUserUnsuccessful()
    {
        $this->customerData = $this->getFakedCustomerData();

        //test create user throws unauthorized
        $customerResource = new CustomerResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $createdCustomer = $customerResource->save($this->customerData);
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $createdCustomer);

        //test create user throws validation errors
        $customerResource = new CustomerResource($this->paystackHttpClient);
        $createdCustomer = $customerResource->save([]);
        $validationErrors = $createdCustomer->getValidationErrors();

        $this->assertInstanceOf(PaystackValidationException::class, $createdCustomer);
        $this->assertTrue(is_array($validationErrors));
        $this->assertGreaterThanOrEqual(1, count($validationErrors));
    }

    /**
     * @depends testCreateUserSuccessful
     *
     * @param $createdCustomer
     */
    public function testGetCustomerByIdReturnsCustomerDetails($createdCustomer)
    {
        $customerResource = new CustomerResource($this->paystackHttpClient);
        $retrievedCustomer = $customerResource->get($createdCustomer['customer_code']);

        $this->assertEquals($createdCustomer['customer_code'], $retrievedCustomer['customer_code']);
        $this->assertEquals($createdCustomer['first_name'], $retrievedCustomer['first_name']);
        $this->assertEquals($createdCustomer['last_name'], $retrievedCustomer['last_name']);
        $this->assertEquals($createdCustomer['email'], $retrievedCustomer['email']);
        $this->assertEquals($createdCustomer['phone'], $retrievedCustomer['phone']);
    }

    /**
     * @depends testCreateUserSuccessful
     *
     * @param $createdCustomer
     */
    public function testGetCustomerByIdThrowsException($createdCustomer)
    {
        //test get user throws unauthorized
        $customerResource = new CustomerResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $retrieveUser = $customerResource->get($createdCustomer['customer_code']);
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $retrieveUser);
    }

    /**
     * @depends testCreateUserSuccessful
     *
     * @param $createdCustomer
     */
    public function testUpdateCustomerByIsSuccessful($createdCustomer)
    {
        $customerResource = new CustomerResource($this->paystackHttpClient);
        $updatedCustomer = $customerResource->update($createdCustomer['customer_code'], ['last_name' => 'new_last_name_e']);

        $this->assertEquals($createdCustomer['customer_code'], $updatedCustomer['customer_code']);
        $this->assertEquals($createdCustomer['first_name'], $updatedCustomer['first_name']);
        $this->assertEquals('new_last_name_e', $updatedCustomer['last_name']);
        $this->assertEquals($createdCustomer['email'], $updatedCustomer['email']);
        $this->assertEquals($createdCustomer['phone'], $updatedCustomer['phone']);
    }

    /**
     * @depends testCreateUserSuccessful
     *
     * @param $createdCustomer
     */
//    public function testUpdateCustomerThrowsException($createdCustomer)
//    {
//        $customerResource = new CustomerResource($this->paystackHttpClient);
//        $updatedCustomer = $customerResource->update($createdCustomer['customer_code'], ['email' => 'this-is-an-invalid-email']);
//        $validationErrors = $updatedCustomer->getValidationErrors();
//
//        $this->assertInstanceOf(PaystackValidationException::class, $updatedCustomer);
//        $this->assertTrue(is_array($validationErrors));
//        $this->assertGreaterThanOrEqual(1, count($validationErrors));
//    }

    /**
     * @depends testCreateUserSuccessful
     *
     * @param $createdCustomer
     */
    public function testDeleteCustomerReturns404($createdCustomer)
    {
        $customerResource = new CustomerResource($this->paystackHttpClient);
        $deleteCustomer = $customerResource->delete($createdCustomer['customer_code']);

        $this->assertInstanceOf(PaystackNotFoundException::class, $deleteCustomer);
    }

    public function testGetAllCustomers()
    {
        $customerResource = new CustomerResource(PaystackHttpClientFactory::make());
        $retrieveUsers = $customerResource->getAll();
        $this->assertTrue(is_array($retrieveUsers));
        $this->assertGreaterThanOrEqual(1, count($retrieveUsers));
    }

    public function testGetAllCustomersThrowsException()
    {
        $customerResource = new CustomerResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $retrieveUsers = $customerResource->getAll(1);
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $retrieveUsers);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
