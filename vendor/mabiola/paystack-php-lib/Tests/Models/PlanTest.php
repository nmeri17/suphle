<?php
/**
 * Created by Malik Abiola.
 * Date: 15/02/2016
 * Time: 13:35
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackUnsupportedOperationException;
use MAbiola\Paystack\Exceptions\PaystackValidationException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Models\Plan;
use MAbiola\Paystack\Repositories\PlanResource;

class PlanTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->planResource = new PlanResource(PaystackHttpClientFactory::make());
    }

    public function testCreatePlanSuccessful()
    {
        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('save')->withAnyArgs()->andReturn($this->planCreatedResourceResponseData);

        $plan = new Plan($mockPlanResource);
        $createdPlan = $plan->make(
            $this->planData['name'],
            $this->planData['description'],
            $this->planData['amount'],
            $this->planData['currency']
        )->save();

        $this->assertInstanceOf(Plan::class, $createdPlan);
        $this->assertArraySubset($this->planData, $createdPlan->get(['name', 'description', 'amount', 'currency']));
    }

    public function testCreatePlanReturnsException()
    {
        $errorResponse = new \stdClass();
        $errorResponse->message = 'Paystack Validation Exception';

        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('save')
            ->withAnyArgs()
            ->andReturn(
                new PaystackValidationException(
                    $errorResponse,
                    400
                )
            );

        $plan = new Plan($mockPlanResource);

        $this->setExpectedException(PaystackValidationException::class);

        $createdPlan = $plan->make(
            $this->planData['name'],
            $this->planData['description'],
            $this->planData['amount'],
            $this->planData['currency']
        )->save();
    }

    public function testCreatePlanOnEmptyPlanObject()
    {
        $plan = new Plan($this->planResource);

        $this->setExpectedException(\InvalidArgumentException::class);

        $plan->save();
    }

    public function testUpdatePlanSuccessfully()
    {
        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('get')->withAnyArgs()->andReturn($this->planRetrievedResourceResponseData);
        $mockPlanResource->shouldReceive('update')->withAnyArgs()->andReturn($this->planUpdatedResourceResponseData);

        $plan = new Plan($mockPlanResource);
        $updatedPlan = $plan->getPlan($this->planRetrievedResourceResponseData['plan_code'])
            ->setUpdateData(['description' => 'new plan description'])->save();

        $this->assertInstanceOf(Plan::class, $updatedPlan);
        $this->assertEquals('new plan description', $updatedPlan->get('description'));
    }

    public function testRetrievePlanSuccessfully()
    {
        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('get')->withAnyArgs()->andReturn($this->planRetrievedResourceResponseData);

        $plan = new Plan($mockPlanResource);
        $retrievedPlan = $plan->getPlan($this->planRetrievedResourceResponseData['plan_code']);

        $this->assertInstanceOf(Plan::class, $retrievedPlan);
        $this->assertArraySubset($this->planData, $retrievedPlan->get(['name', 'description', 'amount', 'currency']));
    }

    public function testRetrievePlanThrowsException()
    {
        $errorResponse = new \stdClass();
        $errorResponse->message = 'Paystack Validation Exception';

        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn(
                new PaystackValidationException(
                    $errorResponse,
                    400
                )
            );

        $plan = new Plan($mockPlanResource);

        $this->setExpectedException(PaystackValidationException::class);

        $retrievedPlan = $plan->getPlan($this->planRetrievedResourceResponseData['plan_code']);
    }

    public function testTransformPlan()
    {
        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('get')->withAnyArgs()->andReturn($this->planRetrievedResourceResponseData);

        $plan = new Plan($mockPlanResource);
        $retrievedPlan = $plan->getPlan($this->planRetrievedResourceResponseData['plan_code'])->transform();
        $this->assertArraySubset($this->planData, $retrievedPlan);
    }

    public function testDeletePlanThrowsUnsupportedException()
    {
        $this->setExpectedException(PaystackUnsupportedOperationException::class);

        $mockPlanResource = \Mockery::mock($this->planResource)->makePartial();
        $mockPlanResource->shouldReceive('get')->withAnyArgs()->andReturn($this->planRetrievedResourceResponseData);

        $plan = new Plan($mockPlanResource);
        $plan->getPlan($this->planRetrievedResourceResponseData['plan_code'])->delete();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
