<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use DateTimeZone;
use Exception;
use Omnipay\BlueSnap\Chargeback;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\Gateway;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;

class ReportingFetchTransactionsRequestTest extends OmnipayBlueSnapTestCase
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
        $this->assertSame($expected, $request->getEndpoint());
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

        $params_with_chargeback = array_merge(array('transactionType' => Constants::TRANSACTION_TYPE_CHARGEBACK), $default_params);
        $params_with_refund = array_merge(array('transactionType' => Constants::TRANSACTION_TYPE_REFUND), $default_params);
        $params_with_sale = array_merge(array('transactionType' => Constants::TRANSACTION_TYPE_SALE), $default_params);

        return array(
            array($default_params, $expected_end_point),
            array($params_with_chargeback, $expected_end_point . '&transactionType=' . Constants::TRANSACTION_TYPE_CHARGEBACK),
            array($params_with_refund, $expected_end_point . '&transactionType=' . Constants::TRANSACTION_TYPE_REFUND),
            array($params_with_sale, $expected_end_point . '&transactionType=' . Constants::TRANSACTION_TYPE_SALE)
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

        $this->setMockHttpResponse('ReportingFetchTransactionsSuccess.txt', $replacements);
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
        $this->assertNull($response->getErrorMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);

        $this->setMockHttpResponse('ReportingFetchTransactionsFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame('Invalid Date Range', $response->getErrorMessage());
    }

    /**
     * @psalm-suppress PossiblyNullArrayAccess
     * @return void
     */
    public function testSendSuccessForRefunds()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);
        $this->request->setTransactionType(Constants::TRANSACTION_TYPE_REFUND);

        $currency = $this->faker->currency();
        $sale_transaction_reference = $this->faker->transactionReference();
        $refund_transaction_reference = $this->faker->transactionReference();
        $refund_date = $this->faker->datetime();
        $sale_date = $this->faker->datetime();

        $this->setMockHttpResponse('ReportingFetchTransactionsWithReversalSuccess.txt', array(
            'SALE_TRANSACTION_REFERENCE' => $sale_transaction_reference,
            'REVERSAL_TRANSACTION_REFERENCE' => $refund_transaction_reference,
            'SALE_DATE' => $sale_date->format('m/d/Y'),
            'REVERSAL_DATE' => $refund_date->format('m/d/Y'),
            'REVERSAL_TYPE' => Constants::TRANSACTION_TYPE_REFUND,
            'CURRENCY' => $currency,
            'AMOUNT' => $this->faker->monetaryAmount($currency),
            'CUSTOMER_REFERENCE' => $this->faker->customerReference(),
            'CUSTOM_0_0' => $this->faker->customParameter(),
            'CUSTOM_1_0' => $this->faker->customParameter()

        ));

        $response = $this->request->send();
        $this->assertCount(2, $response->getTransactions());
        $this->assertEmpty($response->getChargebacks());
        $refunds = $response->getRefunds();
        $this->assertCount(1, $refunds);

        /** @var Refund $refund */
        $refund = $refunds[0];
        $this->assertInstanceOf(Refund::class, $refund);
        $this->assertSame($sale_transaction_reference, $refund->getTransactionReference());
        $this->assertSame($refund_transaction_reference, $refund->getRefundReference());

        /** @var DateTime $actual_date */
        $actual_date =  $refund->getTime();
        $this->assertSame($refund_date->format('m/d/Y'), $actual_date->format('m/d/Y'));
    }

    /**
     * @psalm-suppress PossiblyNullArrayAccess
     * @return void
     */
    public function testSendSuccessForChargebacks()
    {
        $this->request->setStartTime($this->startTime);
        $this->request->setEndTime($this->endTime);
        $this->request->setTransactionType(Constants::TRANSACTION_TYPE_CHARGEBACK);

        $currency = $this->faker->currency();
        $sale_transaction_reference = $this->faker->transactionReference();
        $refund_transaction_reference = $this->faker->transactionReference();
        $chargeback_date = $this->faker->datetime();
        $sale_date = $this->faker->datetime();

        $this->setMockHttpResponse('ReportingFetchTransactionsWithReversalSuccess.txt', array(
            'SALE_TRANSACTION_REFERENCE' => $sale_transaction_reference,
            'REVERSAL_TRANSACTION_REFERENCE' => $refund_transaction_reference,
            'SALE_DATE' => $sale_date->format('m/d/Y'),
            'REVERSAL_DATE' => $chargeback_date->format('m/d/Y'),
            'REVERSAL_TYPE' => Constants::TRANSACTION_TYPE_CHARGEBACK,
            'CURRENCY' => $currency,
            'AMOUNT' => $this->faker->monetaryAmount($currency),
            'CUSTOMER_REFERENCE' => $this->faker->customerReference(),
            'CUSTOM_0_0' => $this->faker->customParameter(),
            'CUSTOM_1_0' => $this->faker->customParameter()

        ));

        $response = $this->request->send();
        $this->assertCount(2, $response->getTransactions());
        $this->assertEmpty($response->getRefunds());
        $chargebacks = $response->getChargebacks();
        $this->assertCount(1, $chargebacks);

        /** @var Chargeback $chargeback */
        $chargeback = $chargebacks[0];
        $this->assertInstanceOf(Chargeback::class, $chargeback);
        $this->assertSame($sale_transaction_reference, $chargeback->getTransactionReference());
        $this->assertSame($refund_transaction_reference, $chargeback->getChargebackReference());

        /** @var DateTime $actual_date */
        $actual_date =  $chargeback->getProcessorReceivedTime();
        $this->assertSame($chargeback_date->format('m/d/Y'), $actual_date->format('m/d/Y'));
    }
}
