<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedFetchSubscriptionsRequestTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedFetchSubscriptionsRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $customerReference;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->customerReference = $this->faker->customerReference();

        $this->request = new ExtendedFetchSubscriptionsRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );
        $this->request->setCustomerReference($this->customerReference);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/tools/shopper-subscriptions-retriever'
            . '?shopperid=' . strval($this->customerReference)
            . '&fulldescription=true',
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
     * @expectedExceptionMessage The customerReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataCustomerRequired()
    {
        $this->request->setCustomerReference(null);
        $this->request->getData();
    }


    /**
     * @return void
     */
    public function testSendSuccess()
    {
        /**
         * @var array<int, array<string, string>>
         */
        $fakeSubscriptions = array();
        for ($i = 1; $i <= 2; $i++) {
            $customerReference = $this->customerReference;
            $currency = $this->faker->currency();
            $fakeSubscriptions[$i] = array(
                'CUSTOMER_REFERENCE' => $customerReference,
                'SUBSCRIPTION_REFERENCE' => $this->faker->subscriptionReference(),
                'CURRENCY' => $currency,
                'AMOUNT' => $this->faker->monetaryAmount($currency),
                'STATUS' => $this->faker->subscriptionStatus(),
                'PLAN_REFERENCE' => $this->faker->planReference()
            );
        }

        $replacements = array();
        foreach ($fakeSubscriptions as $i => $row) {
            foreach ($row as $field => $value) {
                $replacements[$field . '_' . (string) $i] = $value;
            }
        }

        $this->setMockHttpResponse('ExtendedFetchSubscriptionsSuccess.txt', $replacements);

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $subscriptions = $response->getSubscriptions();
        $this->assertSame(2, count($subscriptions));
        if ($subscriptions) {
            foreach ($subscriptions as $i => $subscription) {
                $fakeSubscription = $fakeSubscriptions[intval($i) + 1];
                $this->assertSame(
                    $fakeSubscription['SUBSCRIPTION_REFERENCE'],
                    $subscription->getSubscriptionReference()
                );
                $this->assertSame($fakeSubscription['CURRENCY'], $subscription->getCurrency());
                $this->assertSame($fakeSubscription['AMOUNT'], $subscription->getAmount());
            }
        }
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedFetchSubscriptionsFailure.txt', array(
            'CUSTOMER_REFERENCE' => $this->customerReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'User API_1234567890123456789012 is not authorized to retrieve subscription history for SHOPPER ID '
                . $this->customerReference . '.',
            $response->getMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
