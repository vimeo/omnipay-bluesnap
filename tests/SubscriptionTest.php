<?php

namespace Omnipay\BlueSnap;

use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * @var string
     */
    protected $subscriptionReference;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->subscription = new Subscription();
        $this->subscriptionReference = $this->faker->subscriptionReference();
    }

    /**
     * @return void
     */
    public function testConstructWithParams()
    {
        $subscription = new Subscription(array(
            'subscriptionReference' => $this->subscriptionReference
        ));
        $this->assertSame($this->subscriptionReference, $subscription->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testInitializeWithParams()
    {
        $this->assertSame($this->subscription, $this->subscription->initialize(array(
            'subscriptionReference' => $this->subscriptionReference
        )));
        $this->assertSame($this->subscriptionReference, $this->subscription->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testGetParameters()
    {
        $this->assertSame(
            $this->subscription,
            $this->subscription->setSubscriptionReference($this->subscriptionReference)
        );
        $this->assertSame(
            array('subscriptionReference' => $this->subscriptionReference),
            $this->subscription->getParameters()
        );
    }

    /**
     * @return void
     */
    public function testSubscriptionReference()
    {
        $this->assertSame(
            $this->subscription,
            $this->subscription->setSubscriptionReference($this->subscriptionReference)
        );
        $this->assertSame($this->subscriptionReference, $this->subscription->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $currency = $this->faker->currency();
        $this->assertSame($this->subscription, $this->subscription->setCurrency($currency));
        $this->assertSame($currency, $this->subscription->getCurrency());
    }

    /**
     * @return void
     */
    public function testAmount()
    {
        $amount = $this->faker->monetaryAmount($this->faker->currency());
        $this->assertSame($this->subscription, $this->subscription->setAmount($amount));
        $this->assertSame($amount, $this->subscription->getAmount());
    }

    /**
     * @return void
     */
    public function testStatus()
    {
        $status = $this->faker->subscriptionStatus();
        $this->assertSame($this->subscription, $this->subscription->setStatus($status));
        $this->assertSame($status, $this->subscription->getStatus());
    }
}
