<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTime;
use DateTimeZone;

class FetchCanceledSubscriptionsRequestTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var FetchCanceledSubscriptionsRequest
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

        $this->request = new FetchCanceledSubscriptionsRequest($this->getHttpClient(), $this->getHttpRequest());
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
            'https://sandbox.bluesnap.com/services/2/report/CanceledSubscriptions?period=CUSTOM'
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
     */
    public function testStartTimeWrongTimeZone()
    {
        $startTime = $this->faker->datetime();
        $startTime->setTimezone(new DateTimeZone('Europe/London'));
        $this->request->setStartTime($startTime);
    }

    /**
     * @return void
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
     */
    public function testEndTimeWrongTimeZone()
    {
        $endTime = $this->faker->datetime();
        $endTime->setTimezone(new DateTimeZone('Europe/London'));
        $this->request->setEndTime($endTime);
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

        $this->setMockHttpResponse('FetchCanceledSubscriptionsSuccess.txt', $replacements);
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

        $this->setMockHttpResponse('FetchCanceledSubscriptionsFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame('Invalid Date Range', $response->getMessage());
    }
}
