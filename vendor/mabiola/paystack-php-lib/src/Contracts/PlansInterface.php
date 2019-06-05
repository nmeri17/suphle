<?php
/**
 * Created by Malik Abiola.
 * Date: 06/02/2016
 * Time: 17:13
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Contracts;

interface PlansInterface
{
    const PLAN_CURRENCY_NGN = 'NGN';
    const PLAN_CURRENCY_USD = 'USD';

    const PLAN_INTERVAL_HOURLY = 'hourly';
    const PLAN_INTERVAL_DAILY = 'daily';
    const PLAN_INTERVAL_WEEKLY = 'weekly';
    const PLAN_INTERVAL_FORTNIGHTLY = 'fortnightly';
    const PLAN_INTERVAL_MONTHLY = 'monthly';
    const PLAN_INTERVAL_ANNUALLY = 'annually';
}
