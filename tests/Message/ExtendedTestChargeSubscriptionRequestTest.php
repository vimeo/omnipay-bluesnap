<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedTestChargeSubscriptionRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedTestChargeSubscriptionRequest
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

        $this->request = new ExtendedTestChargeSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference($this->subscriptionReference);
        $this->request->setTestMode(true);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/subscriptions/'
                . strval($this->subscriptionReference) . '/run-specific',
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
     * @expectedExceptionMessage You cannot make a test subscription charge if you're not in test mode
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataTestModeRequired()
    {
        $this->request->setTestMode(false);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ExtendedTestChargeSubscriptionSuccess.txt');

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('204', $response->getCode());
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedTestChargeSubscriptionFailure.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'Call to runSpecificSubscription failed, subscriptionId ' . $this->subscriptionReference . '.',
            $response->getMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
