<?php

namespace Omnipay\BlueSnap\Message;

use Mockery;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTimeZone;

class AbstractRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var AbstractRequest
     */
    protected $request;

    /**
     * @return void
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function setUp()
    {
        $this->faker = new DataFaker();

        /**
         * @var AbstractRequest
         */
        $this->request = Mockery::mock('\Omnipay\BlueSnap\Message\AbstractRequest')->makePartial();
        $this->request->initialize();
        $this->request->setTestMode(true);
    }

    /**
     * @return void
     */
    public function testUsername()
    {
        $username = $this->faker->username();
        $this->assertSame($this->request, $this->request->setUsername($username));
        $this->assertSame($username, $this->request->getUsername());
    }

    /**
     * @return void
     */
    public function testPassword()
    {
        $password = $this->faker->password();
        $this->assertSame($this->request, $this->request->setPassword($password));
        $this->assertSame($password, $this->request->getPassword());
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame('https://sandbox.bluesnap.com/services/2', $this->request->getEndpoint());
        $this->request->setTestMode(false);
        $this->assertSame('https://ws.bluesnap.com/services/2', $this->request->getEndpoint());
    }

    /**
     * @return void
     */
    public function testCustomerReference()
    {
        $customerReference = $this->faker->customerReference();
        $this->assertSame($this->request, $this->request->setCustomerReference($customerReference));
        $this->assertSame($customerReference, $this->request->getCustomerReference());
    }

    /**
     * @return void
     */
    public function testTransactionReference()
    {
        $transactionReference = $this->faker->transactionReference();
        $this->assertSame($this->request, $this->request->setTransactionReference($transactionReference));
        $this->assertSame($transactionReference, $this->request->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testSubscriptionReference()
    {
        $subscriptionReference = $this->faker->subscriptionReference();
        $this->assertSame($this->request, $this->request->setSubscriptionReference($subscriptionReference));
        $this->assertSame($subscriptionReference, $this->request->getSubscriptionReference());
    }

    /**
     * @return void
     */
    public function testSubscriptionChargeReference()
    {
        $subscriptionChargeReference = $this->faker->subscriptionChargeReference();
        $this->assertSame($this->request, $this->request->setSubscriptionChargeReference($subscriptionChargeReference));
        $this->assertSame($subscriptionChargeReference, $this->request->getSubscriptionChargeReference());
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $currency = $this->faker->currency();
        $this->assertSame($this->request, $this->request->setCurrency($currency));
        $this->assertSame($currency, $this->request->getCurrency());
    }

    /**
     * @return void
     */
    public function testAmount()
    {
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $this->request->setCurrency($currency);
        $this->assertSame($this->request, $this->request->setAmount($amount));
        $this->assertSame($amount, $this->request->getAmount());
    }

    /**
     * @return void
     */
    public function testNextChargeDate()
    {
        $nextChargeDate = $this->faker->datetime();
        $this->assertSame($this->request, $this->request->setNextChargeDate($nextChargeDate));
        $this->assertSame($nextChargeDate, $this->request->getNextChargeDate());
    }

    /**
     * @return void
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Dates must be provided in the Etc/GMT+8 time zone
     */
    public function testNextChargeDateWrongTimeZone()
    {
        $nextChargeDate = $this->faker->datetime();
        $nextChargeDate->setTimezone(new DateTimeZone('Europe/London'));
        $this->request->setNextChargeDate($nextChargeDate);
    }

    /**
     * @return void
     */
    public function testReturnUrl()
    {
        $returnUrl = $this->faker->url();
        $this->assertSame($this->request, $this->request->setReturnUrl($returnUrl));
        $this->assertSame($returnUrl, $this->request->getReturnUrl());
    }

    /**
     * @return void
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testStartTime()
    {
        $startTime = $this->faker->datetime();
        $this->assertSame($this->request, $this->request->setStartTime($startTime));
        $this->assertSame($startTime, $this->request->getStartTime());
    }

    /**
     * @return void
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Dates must be provided in the Etc/GMT+8 time zone
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testStartTimeWrongTimeZone()
    {
        $startTime = $this->faker->datetime();
        $startTime->setTimezone(new DateTimeZone('Europe/London'));
        $this->request->setStartTime($startTime);
    }

    /**
     * @return void
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testEndTime()
    {
        $endTime = $this->faker->datetime();
        $this->assertSame($this->request, $this->request->setEndTime($endTime));
        $this->assertSame($endTime, $this->request->getEndTime());
    }

    /**
     * @return void
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Dates must be provided in the Etc/GMT+8 time zone
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testEndTimeWrongTimeZone()
    {
        $endTime = $this->faker->datetime();
        $endTime->setTimezone(new DateTimeZone('Europe/London'));
        $this->request->setEndTime($endTime);
    }
}
