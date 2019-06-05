<?php

namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackUnsupportedOperationException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Models\Customer;
use MAbiola\Paystack\Repositories\CustomerResource;

/**
 * Created by Malik Abiola.
 * Date: 14/02/2016
 * Time: 10:55
 * IDE: PhpStorm.
 */
class CustomerTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->customerResource = new CustomerResource(PaystackHttpClientFactory::make());
    }

    public function testSaveCustomerReturnsCustomer()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('save')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerCreateResponseData);

        $customerModel = new Customer($mockCustomerResource);
        $createdCustomer = $customerModel->make(
            $this->customerData['first_name'],
            $this->customerData['last_name'],
            $this->customerData['email'],
            $this->customerData['phone']
        )->save();

        $this->assertInstanceOf(Customer::class, $createdCustomer);
        $this->assertEquals($this->customerData['first_name'], $createdCustomer->get('first_name'));
        $this->assertEquals($this->customerData['last_name'], $createdCustomer->get('last_name'));
        $this->assertEquals($this->customerData['email'], $createdCustomer->get('email'));
        $this->assertEquals($this->customerData['phone'], $createdCustomer->get('phone'));
    }

    public function testGetCustomerReturnsCustomer()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerRetrievedResponseData);

        $customerModel = new Customer($mockCustomerResource);
        $retrievedCustomer = $customerModel->getCustomer($this->customerCreateResponseData['customer_code']);

        $this->assertInstanceOf(Customer::class, $retrievedCustomer);
        $this->assertEquals($this->customerData['first_name'], $retrievedCustomer->get('first_name'));
        $this->assertEquals($this->customerData['last_name'], $retrievedCustomer->get('last_name'));
        $this->assertEquals($this->customerData['email'], $retrievedCustomer->get('email'));
        $this->assertEquals($this->customerData['phone'], $retrievedCustomer->get('phone'));
    }

    public function testGetCustomerThrowsException()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn(new \Exception());

        $this->setExpectedException(\Exception::class);
        $customerModel = new Customer($mockCustomerResource);
        $customerModel->getCustomer($this->customerCreateResponseData['customer_code']);
    }

    public function testUpdateCustomerReturnsCustomer()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerRetrievedResponseData);

        $mockCustomerResource->shouldReceive('update')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerUpdatedResponseData);

        $customerModel = new Customer($mockCustomerResource);
        $updatedCustomer = $customerModel->getCustomer($this->customerCreateResponseData['customer_code'])
            ->setUpdateData(['last_name' => 'new_last_name'])->save();

        $this->assertInstanceOf(Customer::class, $updatedCustomer);
        $this->assertEquals('new_last_name', $updatedCustomer->get('last_name'));
    }

    public function testUpdateCustomerThrowsException()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerRetrievedResponseData);

        $mockCustomerResource->shouldReceive('update')
            ->withAnyArgs()
            ->once()
            ->andReturn(new \Exception());

        $this->setExpectedException(\Exception::class);

        $customerModel = new Customer($mockCustomerResource);
        $customerModel->getCustomer($this->customerCreateResponseData['customer_code'])
            ->setUpdateData(['last_name' => 'new_last_name'])->save();
    }

    public function testUpdateWithInvalidParamsThrowException()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerRetrievedResponseData);

        $this->setExpectedException(\Exception::class);

        $customerModel = new Customer($mockCustomerResource);
        $customerModel->getCustomer($this->customerCreateResponseData['customer_code'])
            ->setUpdateData([])->save();
    }

    public function testDeleteCustomerReturnsException()
    {
        $mockCustomerResource = \Mockery::mock($this->customerResource)->makePartial();
        $mockCustomerResource->shouldReceive('get')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->customerRetrievedResponseData);

        $this->setExpectedException(PaystackUnsupportedOperationException::class);

        $customerModel = new Customer($mockCustomerResource);
        $customerModel->getCustomer($this->customerCreateResponseData['customer_code'])->delete();
    }

    public function testAttemptToSaveEmptyDataThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $customerModel = new Customer($this->customerResource);
        $customerModel->save();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
