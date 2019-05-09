<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTime;
use DateTimeZone;
use Mockery;

class ExtendedRefundRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedRefundRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $transactionReference;

    /**
     * @var string
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var bool
     */
    protected $cancelSubscriptions;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->transactionReference = $this->faker->transactionReference();
        $this->currency = $this->faker->currency();
        $this->amount = $this->faker->monetaryAmount($this->currency);
        $this->reason = $this->faker->randomCharacters(
            DataFaker::ALPHABET_LOWER . ' ',
            $this->faker->intBetween(6, 30)
        );
        $this->cancelSubscriptions = $this->faker->bool();
        $this->request = new ExtendedRefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransactionReference($this->transactionReference);
        $this->request->setCurrency($this->currency);
        $this->request->setAmount($this->amount);
        $this->request->setReason($this->reason);
        $this->request->setCancelSubscriptions($this->cancelSubscriptions);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/orders/refund'
                . '?invoiceId=' . $this->transactionReference
                . '&amount=' . urlencode((string) $this->amount)
                . '&reason=' . urlencode((string) $this->reason)
                . '&cancelSubscriptions=' . ($this->cancelSubscriptions ? 'true' : 'false'),
            $this->request->getEndpoint()
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testEndpointDefaultCancelSubscriptions()
    {
        $this->request->setCancelSubscriptions(null);

        // @codingStandardsIgnoreStart
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/orders/refund'
                . '?invoiceId=' . $this->transactionReference
                . '&amount=' . urlencode((string) $this->amount)
                . '&reason=' . urlencode((string) $this->reason)
                . '&cancelSubscriptions=false',
            $this->request->getEndpoint()
        );
        // @codingStandardsIgnoreEnd
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
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testReason()
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedRefundRequest
         */
        $request = Mockery::mock('\Omnipay\BlueSnap\Message\ExtendedRefundRequest')->makePartial();
        $request->initialize();

        $this->assertSame($request, $request->setReason($this->reason));
        $this->assertSame($this->reason, $request->getReason());
    }

    /**
     * @return void
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function testCancelSubscriptions()
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedRefundRequest
         */
        $request = Mockery::mock('\Omnipay\BlueSnap\Message\ExtendedRefundRequest')->makePartial();
        $request->initialize();

        $this->assertSame($request, $request->setCancelSubscriptions($this->cancelSubscriptions));
        $this->assertSame($this->cancelSubscriptions, $request->getCancelSubscriptions());
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
     * @expectedExceptionMessage The transactionReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataTransactionReferenceRequired()
    {
        $this->request->setTransactionReference(null);
        $this->request->getData();
    }


    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ExtendedRefundSuccess.txt');
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
        $this->setMockHttpResponse('ExtendedRefundFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame('Invoice has already been fully refunded.', $response->getMessage());
    }
}
