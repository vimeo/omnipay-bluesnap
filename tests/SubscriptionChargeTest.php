<?php

namespace Omnipay\BlueSnap;

use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\Tests\TestCase;

class SubscriptionChargeTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var SubscriptionCharge
     */
    protected $subscriptionCharge;

    /**
     * @var string
     */
    protected $transactionReference;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->subscriptionCharge = new SubscriptionCharge();
        $this->transactionReference = $this->faker->transactionReference();
    }

    /**
     * @return void
     */
    public function testConstructWithParams()
    {
        $subscriptionCharge = new SubscriptionCharge(array(
            'transactionReference' => $this->transactionReference
        ));
        $this->assertSame($this->transactionReference, $subscriptionCharge->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testInitializeWithParams()
    {
        $this->assertSame($this->subscriptionCharge, $this->subscriptionCharge->initialize(array(
            'transactionReference' => $this->transactionReference
        )));
        $this->assertSame($this->transactionReference, $this->subscriptionCharge->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testGetParameters()
    {
        $this->assertSame(
            $this->subscriptionCharge,
            $this->subscriptionCharge->setTransactionReference($this->transactionReference)
        );
        $this->assertSame(
            array('transactionReference' => $this->transactionReference),
            $this->subscriptionCharge->getParameters()
        );
    }

    /**
     * @return void
     */
    public function testTransactionReference()
    {
        $this->assertSame(
            $this->subscriptionCharge,
            $this->subscriptionCharge->setTransactionReference($this->transactionReference)
        );
        $this->assertSame($this->transactionReference, $this->subscriptionCharge->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $currency = $this->faker->currency();
        $this->assertSame($this->subscriptionCharge, $this->subscriptionCharge->setCurrency($currency));
        $this->assertSame($currency, $this->subscriptionCharge->getCurrency());
    }

    /**
     * @return void
     */
    public function testAmount()
    {
        $amount = $this->faker->monetaryAmount($this->faker->currency());
        $this->assertSame($this->subscriptionCharge, $this->subscriptionCharge->setAmount($amount));
        $this->assertSame($amount, $this->subscriptionCharge->getAmount());
    }

    /**
     * @return void
     */
    public function testSubscriptionReference()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $this->assertSame(
            $this->subscriptionCharge,
            $this->subscriptionCharge->setSubscriptionReference($subscriptionReference)
        );
        $this->assertSame($subscriptionReference, $this->subscriptionCharge->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testCustomerReference()
    {
        $customerReference = $this->faker->customerReference();
        $this->assertSame(
            $this->subscriptionCharge,
            $this->subscriptionCharge->setCustomerReference($customerReference)
        );
        $this->assertSame($customerReference, $this->subscriptionCharge->getCustomerReference());
    }
}
