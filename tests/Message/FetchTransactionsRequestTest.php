<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTime;
use DateTimeZone;
use Mockery;

class FetchTransactionsRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var FetchTransactionsRequest
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

        $this->request = new FetchTransactionsRequest($this->getHttpClient(), $this->getHttpRequest());
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
            'https://sandbox.bluesnap.com/services/2/report/TransactionDetail?period=CUSTOM'
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
        $fakeTransactions = array();
        for ($i = 1; $i <= 2; $i++) {
            $currency = $this->faker->currency();
            $fakeTransactions[$i] = array(
                'TRANSACTION_REFERENCE' => $this->faker->transactionReference(),
                'DATE' => $this->faker->datetime()->format('m/d/Y'),
                'CURRENCY' => $currency,
                'AMOUNT' => $this->faker->monetaryAmount($currency),
                'CUSTOMER_REFERENCE' => $this->faker->customerReference(),
                'CUSTOM_1' => $this->faker->customParameter(),
                'CUSTOM_2' => $this->faker->customParameter()
            );
        }

        $replacements = array();
        foreach ($fakeTransactions as $i => $row) {
            foreach ($row as $field => $value) {
                $replacements[$field . '_' . (string) $i] = $value;
            }
        }

        $this->setMockHttpResponse('FetchTransactionsSuccess.txt', $replacements);
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $transactions = $response->getTransactions();
        $this->assertCount(2, $transactions);
        if ($transactions) {
            foreach ($transactions as $i => $transaction) {
                $fakeTransaction = $fakeTransactions[intval($i) + 1];
                $date = $transaction->getDate();
                $this->assertSame($fakeTransaction['TRANSACTION_REFERENCE'], $transaction->getTransactionReference());
                $this->assertSame($fakeTransaction['DATE'], $date ? $date->format('m/d/Y') : '');
                $this->assertSame($fakeTransaction['CURRENCY'], $transaction->getCurrency());
                $this->assertSame($fakeTransaction['AMOUNT'], $transaction->getAmount());
                $this->assertSame($fakeTransaction['CUSTOMER_REFERENCE'], $transaction->getCustomerReference());
                $this->assertSame($fakeTransaction['CUSTOM_1'], $transaction->getCustomParameter1());
                $this->assertSame($fakeTransaction['CUSTOM_2'], $transaction->getCustomParameter2());
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

        $this->setMockHttpResponse('FetchTransactionsFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame('Invalid Date Range', $response->getMessage());
    }
}
