<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedUpdateSubscriptionRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedUpdateSubscriptionRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $subscriptionReference;

    /**
     * @var string
     */
    protected $planReference;

    /**
     * @var string
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var DateTime
     */
    protected $nextChargeDate;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->subscriptionReference = $this->faker->subscriptionReference();
        $this->planReference = $this->faker->planReference();
        $this->currency = $this->faker->currency();
        $this->amount = $this->faker->monetaryAmount($this->currency);
        $this->nextChargeDate = $this->faker->datetime();

        $this->request = new ExtendedUpdateSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference($this->subscriptionReference);
        $this->request->setPlanReference($this->planReference);
        $this->request->setAmount($this->amount);
        $this->request->setCurrency($this->currency);
        $this->request->setNextChargeDate($this->nextChargeDate);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/subscriptions/' . strval($this->subscriptionReference),
            $this->request->getEndpoint()
        );
    }

    /**
     * @return void
     */
    public function testHttpMethod()
    {
        $this->assertSame('PUT', $this->request->getHttpMethod());
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('subscription', $data->getName());
        $this->assertSame($this->subscriptionReference, (string) $data->{'subscription-id'});
        $this->assertSame($this->planReference, (string) $data->{'underlying-sku-id'});
        $this->assertSame($this->currency, (string) $data->{'override-recurring-charge'}->currency);
        $this->assertSame($this->amount, (string) $data->{'override-recurring-charge'}->amount);
        $this->assertSame($this->nextChargeDate->format('d-M-y'), (string) $data->{'next-charge-date'});
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
        $this->setMockHttpResponse('ExtendedUpdateSubscriptionSuccess.txt');

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('204', $response->getCode());
        $this->assertNull($response->getErrorMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $customerReference = $this->faker->customerReference();

        $this->setMockHttpResponse('ExtendedUpdateSubscriptionFailure.txt', array(
            'CUSTOMER_REFERENCE' => $customerReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'The shopper: ' . $customerReference . ' has not given prior consent to certain'
                . ' additional charges and so this operation cannot be processed.',
            $response->getErrorMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
