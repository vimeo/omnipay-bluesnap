<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedCancelSubscriptionRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedCancelSubscriptionRequest
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

        $this->request = new ExtendedCancelSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference($this->subscriptionReference);
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
        $this->setMockHttpResponse('ExtendedCancelSubscriptionSuccess.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference,
        ));

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
        $subscriptionReference = $this->faker->subscriptionReference();

        $this->setMockHttpResponse('ExtendedCancelSubscriptionFailure.txt', array(
            'SUBSCRIPTION_REFERENCE' => $subscriptionReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'User API_1234567890123456789012 is not authorized to update subscription ID '
                . $subscriptionReference . '.',
            $response->getErrorMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
