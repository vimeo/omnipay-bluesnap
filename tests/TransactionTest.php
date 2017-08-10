<?php

namespace Omnipay\BlueSnap;

use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\Tests\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var Transaction
     */
    protected $transaction;

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
        $this->transaction = new Transaction();
        $this->transactionReference = $this->faker->transactionReference();
    }

    /**
     * @return void
     */
    public function testConstructWithParams()
    {
        $transaction = new Transaction(array(
            'transactionReference' => $this->transactionReference
        ));
        $this->assertSame($this->transactionReference, $transaction->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testInitializeWithParams()
    {
        $this->assertSame($this->transaction, $this->transaction->initialize(array(
            'transactionReference' => $this->transactionReference
        )));
        $this->assertSame($this->transactionReference, $this->transaction->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testGetParameters()
    {
        $this->assertSame($this->transaction, $this->transaction->setTransactionReference($this->transactionReference));
        $this->assertSame(
            array('transactionReference' => $this->transactionReference),
            $this->transaction->getParameters()
        );
    }

    /**
     * @return void
     */
    public function testTransactionReference()
    {
        $this->assertSame($this->transaction, $this->transaction->setTransactionReference($this->transactionReference));
        $this->assertSame($this->transactionReference, $this->transaction->getTransactionReference());
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $currency = $this->faker->currency();
        $this->assertSame($this->transaction, $this->transaction->setCurrency($currency));
        $this->assertSame($currency, $this->transaction->getCurrency());
    }

    /**
     * @return void
     */
    public function testAmount()
    {
        $amount = $this->faker->monetaryAmount($this->faker->currency());
        $this->assertSame($this->transaction, $this->transaction->setAmount($amount));
        $this->assertSame($amount, $this->transaction->getAmount());
    }

    /**
     * @return void
     */
    public function testStatus()
    {
        $status = $this->faker->transactionStatus();
        $this->assertSame($this->transaction, $this->transaction->setStatus($status));
        $this->assertSame($status, $this->transaction->getStatus());
    }

    /**
     * @return void
     */
    public function testCustomerReference()
    {
        $customerReference = $this->faker->customerReference();
        $this->assertSame($this->transaction, $this->transaction->setCustomerReference($customerReference));
        $this->assertSame($customerReference, $this->transaction->getCustomerReference());
    }

    /**
     * @return void
     */
    public function testCustomParameter1()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter1($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter1());
    }

    /**
     * @return void
     */
    public function testCustomParameter2()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter2($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter2());
    }

    /**
     * @return void
     */
    public function testCustomParameter3()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter3($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter3());
    }

    /**
     * @return void
     */
    public function testCustomParameter4()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter4($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter4());
    }

    /**
     * @return void
     */
    public function testCustomParameter5()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter5($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter5());
    }

    /**
     * @return void
     */
    public function testCustomParameter6()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter6($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter6());
    }

    /**
     * @return void
     */
    public function testCustomParameter7()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter7($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter7());
    }

    /**
     * @return void
     */
    public function testCustomParameter8()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter8($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter8());
    }

    /**
     * @return void
     */
    public function testCustomParameter9()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter9($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter9());
    }

    /**
     * @return void
     */
    public function testCustomParameter10()
    {
        $customParameter = $this->faker->customParameter();
        $this->assertSame($this->transaction, $this->transaction->setCustomParameter10($customParameter));
        $this->assertSame($customParameter, $this->transaction->getCustomParameter10());
    }
}
