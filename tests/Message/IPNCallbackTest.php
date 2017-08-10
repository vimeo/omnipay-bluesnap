<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use ReflectionClass;

class IPNCallbackTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->url = $this->faker->url();
        $this->queryString = $this->faker->queryString();
    }

    /**
     * Makes sure that a full URL, query string, or $_POST variable all yield the same result
     *
     * @return void
     */
    public function testInputFormats()
    {
        $queryStringCallback = new IPNCallback($this->queryString);
        $fullUrlCallback = new IPNCallback($this->faker->url() . '?' . $this->queryString);
        parse_str($this->queryString, $post);
        /** @var array<string, string> */
        $post = $post;
        $postCallback = new IPNCallback($post);

        $reflectionIPNCallback = new ReflectionClass('\Omnipay\BlueSnap\Message\IPNCallback');
        $reflectionQueryParams = $reflectionIPNCallback->getProperty('queryParams');
        $reflectionQueryParams->setAccessible(true);

        $queryStringQueryParams = (array) $reflectionQueryParams->getValue($queryStringCallback);
        $fullUrlQueryParams = (array) $reflectionQueryParams->getValue($fullUrlCallback);
        $postQueryParams = (array) $reflectionQueryParams->getValue($postCallback);

        $this->assertSame($queryStringQueryParams, $fullUrlQueryParams);
        $this->assertSame($queryStringQueryParams, $postQueryParams);
    }

    /**
     * @return void
     */
    public function testIsCharge()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=CHARGE');
        $this->assertTrue($callback->isCharge());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=CHARGE&' . $this->queryString);
        $this->assertTrue($callback->isCharge());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isCharge());
    }

    /**
     * @return void
     */
    public function testIsCancellation()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertTrue($callback->isCancellation());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=CANCELLATION&' . $this->queryString);
        $this->assertTrue($callback->isCancellation());
        $callback = new IPNCallback($this->queryString . '&transactionType=CHARGE');
        $this->assertFalse($callback->isCancellation());
    }

    /**
     * @return void
     */
    public function testIsSubscriptionCancellation()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCEL_ON_RENEWAL');
        $this->assertTrue($callback->isCancellationRequest());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=CANCEL_ON_RENEWAL&' . $this->queryString);
        $this->assertTrue($callback->isCancellationRequest());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isCancellationRequest());
    }

    /**
     * @return void
     */
    public function testIsChargeback()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=CHARGEBACK');
        $this->assertTrue($callback->isChargeback());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=CHARGEBACK&' . $this->queryString);
        $this->assertTrue($callback->isChargeback());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isChargeback());
    }

    /**
     * @return void
     */
    public function testIsSubscriptionCharge()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=RECURRING');
        $this->assertTrue($callback->isSubscriptionCharge());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=RECURRING&' . $this->queryString);
        $this->assertTrue($callback->isSubscriptionCharge());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isSubscriptionCharge());
    }

    /**
     * @return void
     */
    public function testIsRefund()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=REFUND');
        $this->assertTrue($callback->isRefund());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=REFUND&' . $this->queryString);
        $this->assertTrue($callback->isRefund());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isRefund());
    }

    /**
     * @return void
     */
    public function testIsChargeFailure()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=CC_CHARGE_FAILED');
        $this->assertTrue($callback->isChargeFailure());
        $callback = new IPNCallback($this->faker->url() . '?transactionType=CC_CHARGE_FAILED&' . $this->queryString);
        $this->assertTrue($callback->isChargeFailure());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isChargeFailure());
    }

    /**
     * @return void
     */
    public function testIsSubscriptionChargeFailure()
    {
        $callback = new IPNCallback($this->queryString . '&transactionType=SUBSCRIPTION_CHARGE_FAILURE');
        $this->assertTrue($callback->isSubscriptionChargeFailure());
        $callback = new IPNCallback(
            $this->faker->url() . '?transactionType=SUBSCRIPTION_CHARGE_FAILURE&' . $this->queryString
        );
        $this->assertTrue($callback->isSubscriptionChargeFailure());
        $callback = new IPNCallback($this->queryString . '&transactionType=CANCELLATION');
        $this->assertFalse($callback->isSubscriptionChargeFailure());
    }

    /**
     * @return void
     */
    public function testTransactionReference()
    {
        $transactionReference = $this->faker->transactionReference();
        $callback = new IPNCallback($this->queryString . '&referenceNumber=' . $transactionReference);
        $this->assertSame($transactionReference, $callback->getTransactionReference());
        $callback = new IPNCallback(
            $this->faker->url() . '?referenceNumber=' . $transactionReference . '&' . $this->queryString
        );
        $this->assertSame($transactionReference, $callback->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testSubscriptionReference()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $callback = new IPNCallback($this->queryString . '&subscriptionId=' . $subscriptionReference);
        $this->assertSame($subscriptionReference, $callback->getSubscriptionReference());
        $callback = new IPNCallback(
            $this->faker->url() . '?subscriptionId=' . $subscriptionReference . '&' . $this->queryString
        );
        $this->assertSame($subscriptionReference, $callback->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testPlanReference()
    {
        $planReference = $this->faker->planReference();
        $callback = new IPNCallback($this->queryString . '&contractId=' . $planReference);
        $this->assertSame($planReference, $callback->getplanReference());
        $callback = new IPNCallback(
            $this->faker->url() . '?contractId=' . $planReference . '&' . $this->queryString
        );
        $this->assertSame($planReference, $callback->getPlanReference());
    }

    /**
     * @return void
     */
    public function testCustomerReference()
    {
        $customerReference = $this->faker->customerReference();
        $callback = new IPNCallback($this->queryString . '&accountId=' . $customerReference);
        $this->assertSame($customerReference, $callback->getCustomerReference());
        $callback = new IPNCallback(
            $this->faker->url() . '?accountId=' . $customerReference . '&' . $this->queryString
        );
        $this->assertSame($customerReference, $callback->getCustomerReference());
    }

    /**
     * @return void
     */
    public function testAmount()
    {
        $amount = $this->faker->monetaryAmount($this->faker->currency());
        $callback = new IPNCallback($this->queryString . '&invoiceAmount=' . $amount);
        $this->assertSame($amount, $callback->getAmount());
        $callback = new IPNCallback(
            $this->faker->url() . '?invoiceAmount=' . $amount . '&' . $this->queryString
        );
        $this->assertSame($amount, $callback->getAmount());
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $currency = $this->faker->currency();
        $callback = new IPNCallback($this->queryString . '&currency=' . $currency);
        $this->assertSame($currency, $callback->getCurrency());
        $callback = new IPNCallback(
            $this->faker->url() . '?currency=' . $currency . '&' . $this->queryString
        );
        $this->assertSame($currency, $callback->getCurrency());
    }

    /**
     * @return void
     */
    public function testDate()
    {
        $date = $this->faker->datetime();
        $dateString = urlencode($date->format('m/d/Y h:i A') ?: '');
        $callback = new IPNCallback($this->queryString . '&transactionDate=' . $dateString);
        $this->assertEquals($date, $callback->getDate());
        $callback = new IPNCallback(
            $this->faker->url() . '?transactionDate=' . $dateString . '&' . $this->queryString
        );
        $this->assertEquals($date, $callback->getDate());
    }

    /**
     * @return void
     */
    public function testGetParameter()
    {
        $parameterString = $this->faker->queryString(1);
        $parameterParts = explode('=', $parameterString);
        $parameterName = $parameterParts[0];
        $parameterValue = $parameterParts[1];
        $callback = new IPNCallback($this->queryString . '&' . $parameterString);
        $this->assertSame($parameterValue, $callback->getParameter($parameterName));
        $callback = new IPNCallback($this->faker->url() . '?' . $parameterString . '&' . $this->queryString);
        $this->assertSame($parameterValue, $callback->getParameter($parameterName));
    }
}
