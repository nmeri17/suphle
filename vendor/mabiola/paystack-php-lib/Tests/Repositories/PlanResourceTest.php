<?php
/**
 * Created by Malik Abiola.
 * Date: 17/02/2016
 * Time: 14:31
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Exceptions\PaystackNotFoundException;
use MAbiola\Paystack\Exceptions\PaystackUnauthorizedException;
use MAbiola\Paystack\Exceptions\PaystackValidationException;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;
use MAbiola\Paystack\Repositories\PlanResource;

class PlanResourceTest extends BaseTestCase
{
    protected $paystackHttpClient;

    public function setUp()
    {
        parent::setUp();
        $this->paystackHttpClient = PaystackHttpClientFactory::make();
    }

    public function testCreatePlanSuccessful()
    {
        $planResource = new PlanResource($this->paystackHttpClient);
        $createdPlan = $planResource->save($this->planData);

        $this->assertTrue(is_array($createdPlan));
        $this->assertEquals($this->planData['amount'], $createdPlan['amount']);
        $this->assertEquals($this->planData['description'], $createdPlan['description']);
        $this->assertEquals($this->planData['name'], $createdPlan['name']);

        return $createdPlan;
    }

    public function testCreatePlanReturnsErrors()
    {
        //test create plan returns unauthorized
        $planResource = new PlanResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $createdPlan = $planResource->save($this->planData);
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $createdPlan);

        //test create plan returns validation errors
        $planResource = new PlanResource($this->paystackHttpClient);
        $createdPlan = $planResource->save([]);

        $this->assertInstanceOf(PaystackValidationException::class, $createdPlan);

        $validationErrors = $createdPlan->getValidationErrors();

        $this->assertTrue(is_array($validationErrors));
        $this->assertGreaterThanOrEqual(1, count($validationErrors));
    }

    /**
     * @depends testCreatePlanSuccessful
     *
     * @param $createdPlan
     */
    public function testUpdatePlanSuccessful($createdPlan)
    {
        $planResource = new PlanResource($this->paystackHttpClient);
        $updatePlan = $planResource->update($createdPlan['plan_code'], ['interval' => 'weekly']);

        $this->assertTrue(is_array($updatePlan));
        $this->assertTrue($updatePlan['status']);
    }

    /**
     * @depends testCreatePlanSuccessful
     *
     * @param $createdPlan
     */
    public function testUpdatePlanThrowsException($createdPlan)
    {
        $planResource = new PlanResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $updatePlan = $planResource->update($createdPlan['plan_code'], ['interval' => 'weekly']);

        $this->assertInstanceOf(PaystackUnauthorizedException::class, $updatePlan);
    }

    /**
     * @depends testCreatePlanSuccessful
     *
     * @param $createdPlan
     */
    public function testGetPlanByIdSuccessful($createdPlan)
    {
        $planResource = new PlanResource($this->paystackHttpClient);
        $retrievedPlan = $planResource->get($createdPlan['plan_code']);

        $this->assertTrue(is_array($retrievedPlan));
        $this->assertEquals($this->planData['amount'], $retrievedPlan['amount']);
        $this->assertEquals($this->planData['description'], $retrievedPlan['description']);
        $this->assertEquals($this->planData['name'], $retrievedPlan['name']);
    }

    /**
     * @depends testCreatePlanSuccessful
     *
     * @param $createdPlan
     */
    public function testGetPlanByIdThrowsException($createdPlan)
    {
        $planResource = new PlanResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $retrievedPlan = $planResource->get($createdPlan['plan_code']);
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $retrievedPlan);
    }

    public function testGetAllPlansSuccessful()
    {
        $planResource = new PlanResource($this->paystackHttpClient);
        $retrievedPlans = $planResource->getAll();

        $this->assertTrue(is_array($retrievedPlans));
        $this->assertGreaterThanOrEqual(1, $retrievedPlans);
        $this->assertArrayHasKey('name', $retrievedPlans[0]);
        $this->assertArrayHasKey('amount', $retrievedPlans[0]);
    }

    public function testGetAllPlansThrowsError()
    {
        $planResource = new PlanResource(PaystackHttpClientFactory::make($this->fakeAuthHeader));
        $retrievedPlans = $planResource->getAll();
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $retrievedPlans);
    }

    /**
     * @depends testCreatePlanSuccessful
     *
     * @param $createdPlan
     */
    public function testDeletePlanReturnsA404($createdPlan)
    {
        $planResource = new PlanResource($this->paystackHttpClient);
        $deletePlan = $planResource->delete($createdPlan['plan_code']);
        $this->assertInstanceOf(PaystackNotFoundException::class, $deletePlan);
    }
}
