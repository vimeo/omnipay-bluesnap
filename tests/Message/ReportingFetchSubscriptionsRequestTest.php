<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTime;
use DateTimeZone;
use Mockery;

class ReportingFetchSubscriptionsRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ReportingFetchSubscriptionsRequest
     */
    protected $request;

    /**
     * @var DateTime
     */
    protected $startTime;

    /**
     * @var DateTime
     */
    protected $endTime;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->startTime = $this->faker->datetime();
        do {
            $this->endTime = $this->faker->datetime();
        } while ($this->endTime == $this->startTime);
        if ($this->endTime < $this->startTime) {
            $temp = $this->endTime;
            $this->endTime = $this->startTime;
            $this->startTime = $temp;
        }

        $this->request = new ReportingFetchSubscriptionsRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);

        // @codingStandardsIgnoreStart
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/report/ActiveSubscriptions?period=CUSTOM'
                . '&from_date=' . urlencode((string) $this->startTime->format('m/d/Y'))
                . '&to_date=' . urlencode((string) $this->endTime->format('m/d/Y')),
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
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);

        $this->assertNull($this->request->getData());
    }

    /**
     * @return void
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage startTime cannot be greater than endTime
     */
    public function testGetDataStartDateGreater()
    {
        $this->request->setStartTime($this->endTime);
        $this->request->setEndTime($this->startTime);

        $this->assertNull($this->request->getData());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The startTime parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataStartTimeRequired()
    {
        $this->request->setEndTime($this->endTime);
        $this->request->getData();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The endTime parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataEndTimeRequired()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);

        /**
         * @var array<int, array<string, string>>
         */
        $fakeSubscriptions = array();
        for ($i = 1; $i <= 2; $i++) {
            $currency = $this->faker->currency();
            $fakeSubscriptions[$i] = array(
                'SUBSCRIPTION_REFERENCE' => $this->faker->subscriptionReference(),
                'CURRENCY' => $currency,
                'AMOUNT' => $this->faker->monetaryAmount($currency)
            );
        }

        $replacements = array();
        foreach ($fakeSubscriptions as $i => $row) {
            foreach ($row as $field => $value) {
                $replacements[$field . '_' . (string) $i] = $value;
            }
        }

        $this->setMockHttpResponse('ReportingFetchSubscriptionsSuccess.txt', $replacements);
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $subscriptions = $response->getSubscriptions();
        $this->assertCount(2, $subscriptions);
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
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);

        $this->setMockHttpResponse('ReportingFetchSubscriptionsFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame('Invalid Date Range', $response->getMessage());
    }
}
