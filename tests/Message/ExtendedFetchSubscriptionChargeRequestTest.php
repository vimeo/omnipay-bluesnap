<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedFetchSubscriptionChargeRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedFetchSubscriptionChargeRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $subscriptionReference;

    /**
     * @var string
     */
    protected $subscriptionChargeReference;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->subscriptionReference = $this->faker->subscriptionReference();
        $this->subscriptionChargeReference = $this->faker->subscriptionChargeReference();

        $this->request = new ExtendedFetchSubscriptionChargeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference($this->subscriptionReference);
        $this->request->setSubscriptionChargeReference($this->subscriptionChargeReference);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/subscriptions/' . $this->subscriptionReference
                . '/subscription-charges/' . $this->subscriptionChargeReference,
            $this->request->getEndpoint()
        );
        // @codingStandardsIgnoreEnd
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
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The subscriptionChargeReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataSubscriptionChargeRequired()
    {
        $this->request->setSubscriptionChargeReference(null);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $dateCreated = $this->faker->timestamp();
        $transactionReference = $this->faker->transactionReference();

        $this->setMockHttpResponse('ExtendedFetchSubscriptionChargeSuccess.txt', array(
            'AMOUNT' => $amount,
            'CURRENCY' => $currency,
            'DATE_CREATED' => $dateCreated,
            'TRANSACTION_REFERENCE' => $transactionReference
        ));
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        // the response does not include the subscriptionChargeReference
        $this->assertSame($currency, $response->getCurrency());
        $this->assertSame($amount, $response->getAmount());
        $responseDateCreated = $response->getDateCreated();
        $this->assertEquals(new DateTime($dateCreated), $responseDateCreated);
        $this->assertEquals(
            'Etc/GMT+8',
            $responseDateCreated ? $responseDateCreated->getTimezone()->getName() : null
        );
        $this->assertSame($transactionReference, $response->getTransactionReference());
        $this->assertNull($response->getErrorMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedFetchSubscriptionChargeFailure.txt', array(
            'SUBSCRIPTION_CHARGE_REFERENCE' => $this->subscriptionChargeReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'Subscription Charge retrieval service failure because subscriptionCharge ID: '
                . $this->subscriptionChargeReference . ' was not found.',
            $response->getErrorMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
