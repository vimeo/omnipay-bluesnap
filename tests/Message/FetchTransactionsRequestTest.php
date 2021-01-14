<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use DateTimeZone;
use Exception;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\Gateway;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Types;

class FetchTransactionsRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ReportingFetchTransactionsRequest
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

        $this->request = new ReportingFetchTransactionsRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    /**
     * @param array $params
     * @param string $expected
     * @dataProvider provideTestEndPointParameters
     * @return void
     */
    public function testEndpoint($params, $expected)
    {
        $gateway = new Gateway();
        $gateway->setTestMode(true);
        $request = $gateway->fetchTransactions($params);

        // @codingStandardsIgnoreStart
        $this->assertSame($expected,$request->getEndpoint());
        // @codingStandardsIgnoreEnd
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function provideTestEndPointParameters()
    {
        $startTime = '01/01/2020';
        $endTime = '01/01/2021';

        $expected_end_point = AbstractRequest::TEST_ENDPOINT . '/services/2/report/TransactionDetail?period=CUSTOM'
            . '&from_date=' . urlencode($startTime)
            . '&to_date=' . urlencode($endTime);
        $default_params = array(
            'startTime' => new DateTime($startTime, new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)),
            'endTime' => new DateTime($endTime, new DateTimeZone(Constants::BLUESNAP_TIME_ZONE))
        );

        $params_with_chargeback = array_merge(array('transactionType' => Types::TRANSACTION_CHARGEBACK), $default_params);
        $params_with_refund = array_merge(array('transactionType' => Types::TRANSACTION_REFUND), $default_params);
        $params_with_sale = array_merge(array('transactionType' => Types::TRANSACTION_SALE), $default_params);

        return array(
            array($default_params, $expected_end_point),
            array($params_with_chargeback, $expected_end_point . '&transactionType=' . Types::TRANSACTION_CHARGEBACK),
            array($params_with_refund, $expected_end_point . '&transactionType=' . Types::TRANSACTION_REFUND),
            array($params_with_sale, $expected_end_point . '&transactionType=' . Types::TRANSACTION_SALE)
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
