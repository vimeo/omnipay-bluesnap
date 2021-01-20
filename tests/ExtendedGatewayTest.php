<?php

namespace Omnipay\BlueSnap;

use Omnipay\Omnipay;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedGatewayTest extends GatewayTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedGateway
     */
    protected $gateway;

    /**
     * @return void
     */
    public function setUp()
    {
        /**
         * @var \Guzzle\Http\ClientInterface
         */
        $httpClient = $this->getHttpClient();
        /**
         * @var \Symfony\Component\HttpFoundation\Request
         */
        $httpRequest = $this->getHttpRequest();
        $this->gateway = new ExtendedGateway($httpClient, $httpRequest);
        $this->gateway->setTestMode(true);
        $this->faker = new DataFaker();
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $this->assertSame('BlueSnap Extended', $this->gateway->getName());
    }

    /**
     * @return void
     * @psalm-suppress MixedAssignment because that is exactly what this function allows
     */
    public function testCreation()
    {
        $gateway = Omnipay::create('BlueSnap_Extended');
        $this->assertInstanceOf('Omnipay\BlueSnap\ExtendedGateway', $gateway);
    }

    /**
     * @return void
     */
    public function testFetchCustomer()
    {
        $customerReference = $this->faker->customerReference();
        $request = $this->gateway->fetchCustomer(
            array(
                'customerReference' => $customerReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedFetchCustomerRequest', $request);
        $this->assertSame($customerReference, $request->getCustomerReference());
    }

    /**
     * @return void
     */
    public function testFetchTransaction()
    {
        $transactionReference = $this->faker->transactionReference();
        $request = $this->gateway->fetchTransaction(
            array(
                'transactionReference' => $transactionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedFetchTransactionRequest', $request);
        $this->assertSame($transactionReference, $request->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testFetchSubscription()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $request = $this->gateway->fetchSubscription(
            array(
                'subscriptionReference' => $subscriptionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testFetchSubscriptionCharge()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $subscriptionChargeReference = $this->faker->subscriptionChargeReference();
        $request = $this->gateway->fetchSubscriptionCharge(
            array(
                'subscriptionReference' => $subscriptionReference,
                'subscriptionChargeReference' => $subscriptionChargeReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionChargeRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
        $this->assertSame($subscriptionChargeReference, $request->getSubscriptionChargeReference());
    }

    /**
     * @return void
     */
    public function testUpdateSubscription()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $nextChargeDate = $this->faker->datetime();

        $request = $this->gateway->updateSubscription(
            array(
                'subscriptionReference' => $subscriptionReference,
                'currency' => $currency,
                'amount' => $amount,
                'nextChargeDate' => $nextChargeDate
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedUpdateSubscriptionRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
        $this->assertSame($currency, $request->getCurrency());
        $this->assertSame($amount, $request->getAmount());
        $this->assertSame($nextChargeDate, $request->getNextChargeDate());
    }

    /**
     * @return void
     */
    public function testCancelSubscription()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $request = $this->gateway->cancelSubscription(
            array(
                'subscriptionReference' => $subscriptionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testReactivateSubscription()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $request = $this->gateway->reactivateSubscription(
            array(
                'subscriptionReference' => $subscriptionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedReactivateSubscriptionRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testTestChargeSubscription()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $request = $this->gateway->testChargeSubscription(
            array(
                'subscriptionReference' => $subscriptionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedTestChargeSubscriptionRequest', $request);
        $this->assertSame($subscriptionReference, $request->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testRefund()
    {
        $transactionReference = $this->faker->transactionReference();
        $request = $this->gateway->refund(
            array(
                'transactionReference' => $transactionReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedRefundRequest', $request);
        $this->assertSame($transactionReference, $request->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testFetchSubscriptionsByTimeRange()
    {
        $startTime = $this->faker->datetime();
        $endTime = $this->faker->datetime();
        $request = $this->gateway->fetchSubscriptions(
            array(
                'startTime' => $startTime,
                'endTime' => $endTime
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ReportingFetchSubscriptionsRequest', $request);
        $this->assertSame($startTime, $request->getStartTime());
        $this->assertSame($endTime, $request->getEndTime());
    }

    /**
     * @return void
     */
    public function testFetchSubscriptionsByCustomer()
    {
        $customerReference = $this->faker->customerReference();
        $request = $this->gateway->fetchSubscriptions(
            array(
                'customerReference' => $customerReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionsRequest', $request);
        $this->assertSame($customerReference, $request->getCustomerReference());
    }
}
