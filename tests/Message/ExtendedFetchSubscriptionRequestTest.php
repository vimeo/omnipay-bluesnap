<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedFetchSubscriptionRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedFetchSubscriptionRequest
     */
    protected $request;

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
        $this->subscriptionReference = $this->faker->subscriptionReference();

        $this->request = new ExtendedFetchSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference($this->subscriptionReference);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/subscriptions/'
            . strval($this->subscriptionReference)
            . '?fulldescription=true',
            $this->request->getEndpoint()
        );
    }

    /**
     * @return void
     */
    public function testHttpMethod()
    {
        $this->assertSame('GET', $this->request->getHttpMethod());
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $this->assertNull($this->request->getData());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The subscriptionReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataSubscriptionRequired()
    {
        $this->request->setSubscriptionReference(null);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $customerReference = $this->faker->customerReference();
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $subscriptionChargeReference = $this->faker->subscriptionChargeReference();
        $nextChargeDate = $this->faker->timestamp();
        $status = $this->faker->subscriptionStatus();
        $fakeCard = $this->faker->card();
        $cardBrand = $fakeCard->getBrand();
        $cardLastFour = $fakeCard->getNumberLastFour();
        $planReference = $this->faker->planReference();
        $chargeCurrency = $this->faker->currency();
        $chargeAmount = $this->faker->monetaryAmount($chargeCurrency);
        $dateCreated = $this->faker->timestamp();
        $transactionReference = $this->faker->transactionReference();

        $this->setMockHttpResponse('ExtendedFetchSubscriptionSuccess.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference,
            'CUSTOMER_REFERENCE' => $customerReference,
            'PLAN_REFERENCE' => $planReference,
            'AMOUNT' => $amount,
            'CURRENCY' => $currency,
            'SUBSCRIPTION_CHARGE_REFERENCE' => $subscriptionChargeReference,
            'NEXT_CHARGE_DATE' => $nextChargeDate,
            'STATUS' => $status,
            'CARD_LAST_FOUR' => $cardLastFour,
            'CARD_BRAND' => ucfirst($cardBrand ?: ''),
            'TRANSACTION_REFERENCE' => $transactionReference,
            'DATE_CREATED' => $dateCreated,
            'CHARGE_AMOUNT' => $chargeAmount,
            'CHARGE_CURRENCY' => $chargeCurrency
        ));

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame($customerReference, $response->getCustomerReference());
        $this->assertSame($planReference, $response->getPlanReference());
        $this->assertSame($currency, $response->getCurrency());
        $this->assertSame($amount, $response->getAmount());
        $this->assertSame($status, $response->getStatus());
        $responseNextChargeDate = $response->getNextChargeDate();
        $this->assertEquals(new DateTime($nextChargeDate), $responseNextChargeDate);
        $this->assertEquals(
            'Etc/GMT+8',
            $responseNextChargeDate ? $responseNextChargeDate->getTimezone()->getName() : null
        );
        $card = $response->getCard();
        $this->assertInstanceOf('\Omnipay\BlueSnap\CreditCard', $card);
        if ($card === null) {
            // so psalm knows the things below won't be hit
            return;
        }
        $subscriptionCharges = $response->getSubscriptionCharges();
        $this->assertNotNull($subscriptionCharges);
        if ($subscriptionCharges === null) {
            // so psalm knows the things below won't be hit
            return;
        }
        foreach ($subscriptionCharges as $charge) {
            $this->assertSame($chargeAmount, $charge->getAmount());
            $this->assertSame($chargeCurrency, $charge->getCurrency());
            $this->assertSame($transactionReference, $charge->getTransactionReference());
            $chargeDate = $charge->getDate();
            $this->assertEquals(new DateTime($dateCreated), $chargeDate);
        }
        $this->assertSame($cardLastFour, $card->getNumberLastFour());
        $this->assertSame($cardBrand, $card->getBrand());
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendWithOverriddenChargeSuccess()
    {
        $customerReference = $this->faker->customerReference();
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $subscriptionChargeReference = $this->faker->subscriptionChargeReference();
        $nextChargeDate = $this->faker->timestamp();

        $this->setMockHttpResponse('ExtendedFetchSubscriptionOverriddenChargeSuccess.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference,
            'CUSTOMER_REFERENCE' => $customerReference,
            'AMOUNT' => $amount,
            'CURRENCY' => $currency,
            'SUBSCRIPTION_CHARGE_REFERENCE' => $subscriptionChargeReference,
            'NEXT_CHARGE_DATE' => $nextChargeDate
        ));

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame($this->subscriptionReference, $response->getSubscriptionReference());
        $this->assertSame($customerReference, $response->getCustomerReference());
        $this->assertSame($currency, $response->getCurrency());
        $this->assertSame($amount, $response->getAmount());
        $responseNextChargeDate = $response->getNextChargeDate();
        $this->assertEquals(new DateTime($nextChargeDate), $responseNextChargeDate);
        $this->assertEquals(
            'Etc/GMT+8',
            $responseNextChargeDate ? $responseNextChargeDate->getTimezone()->getName() : null
        );
        $this->assertNull($response->getMessage());
    }

    /**
     * Tests making a request for a subscription with multiple charges
     * @return void
     */
    public function testSendMultipleChargesSuccess()
    {
        $transactionReference1 = $this->faker->transactionReference();
        $transactionReference2 = $this->faker->transactionReference();

        $this->setMockHttpResponse('ExtendedFetchSubscriptionMultipleChargesSuccess.txt', array(
            'TRANSACTION_REFERENCE_1' => $transactionReference1,
            'TRANSACTION_REFERENCE_2' => $transactionReference2
        ));

        $response = $this->request->send();
        $subscriptionCharges = $response->getSubscriptionCharges();
        $this->assertNotNull($subscriptionCharges);
        if ($subscriptionCharges === null) {
            // so psalm knows the things below won't be hit
            return;
        }
        $references = array();
        foreach ($subscriptionCharges as $charge) {
            $references[] = $charge->getTransactionReference();
        }
        $this->assertSame(
            array($transactionReference1, $transactionReference2),
            $references
        );
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedFetchSubscriptionFailure.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'User API_1234567890123456789012 is not authorized to retrieve subscription ID '
                . $this->subscriptionReference . '.',
            $response->getMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
