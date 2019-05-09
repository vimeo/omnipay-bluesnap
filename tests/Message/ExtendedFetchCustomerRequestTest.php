<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class ExtendedFetchCustomerRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedFetchCustomerRequest
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

        $this->request = new ExtendedFetchCustomerRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference($this->customerReference);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/shoppers/' . strval($this->customerReference),
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
        $this->setMockHttpResponse('ExtendedFetchCustomerSuccess.txt', array(
            'CUSTOMER_REFERENCE' => $this->customerReference
        ));
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame($this->customerReference, $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedFetchCustomerFailure.txt', array(
            'CUSTOMER_REFERENCE' => $this->customerReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('403', $response->getCode());
        $this->assertSame(
            'User: API_1234567890123456789012 is not authorized to view shopper: ' . $this->customerReference . '.',
            $response->getMessage()
        );
    }
}
