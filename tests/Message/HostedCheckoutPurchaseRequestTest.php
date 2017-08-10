<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\UrlParameterBag;
use Omnipay\BlueSnap\UrlParameter;
use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class HostedCheckoutPurchaseRequestTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var HostedCheckoutPurchaseRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $storeReference;

    /**
     * @var string
     */
    protected $planReference;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var UrlParameterBag
     */
    protected $parameters;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->storeReference = $this->faker->storeReference();
        $this->planReference = $this->faker->planReference();
        $this->currency = $this->faker->currency();
        $this->returnUrl = $this->faker->url();
        $this->parameters = $this->faker->urlParameters();

        $this->request = new HostedCheckoutPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTestMode(true);
        $this->request->setStoreReference($this->storeReference);
        $this->request->setPlanReference($this->planReference);
        $this->request->setCurrency($this->currency);
        $this->request->setReturnUrl($this->returnUrl);
        $this->request->setStoreParameters($this->parameters);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/tools/param-encryption',
            $this->request->getEndpoint()
        );
    }

    /**
     * @return void
     */
    public function testHttpMethod()
    {
        $this->assertSame('POST', $this->request->getHttpMethod());
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame('param-encryption', $data->getName());

        // loop through the XML and make sure all the keys and values are present
        $expectedParameters = array(
            array(
                'key' => 'sku' . $this->planReference,
                'value' => '1',
                'found' => false
            ),
            array (
                'key' => 'currency',
                'value' => $this->currency,
                'found' => false
            ),
            array (
                'key' => 'thankyou.backtosellerurl',
                'value' => urlencode($this->returnUrl),
                'found' => false
            )
        );
        /**
         * @var UrlParameter
         */
        foreach ($this->parameters as $parameter) {
            $expectedParameters[] = array(
                'key' => $parameter->getKey(),
                'value' => $parameter->getValue(),
                'found' => false
            );
        }

        $keyJustFound = null;
        $keyCount = 0;
        $valueCount = 0;

        /**
         * @var \SimpleXMLElement
         */
        $parameters = $data->parameters;

        /**
         * @var \SimpleXMLElement
         */
        foreach ($parameters->children() as $parameter) {
            /**
             * @var \SimpleXMLElement
             */
            foreach ($parameter->children() as $keyOrValue) {
                if ($keyJustFound) {
                    $value = $keyOrValue;
                    $valueCount++;

                    // make sure every key has a value
                    $this->assertEquals('param-value', $value->getName());
                    foreach ($expectedParameters as &$expectedParameter) {
                        if ($expectedParameter['key'] === $keyJustFound && !$expectedParameter['found']) {
                            // make sure that value is correct
                            $this->assertEquals($expectedParameter['value'], (string) $value);
                            $expectedParameter['found'] = true;
                            break;
                        }
                    }
                    $keyJustFound = null;
                } else {
                    $key = $keyOrValue;
                    $keyCount++;

                    $this->assertSame('param-key', $key->getName());
                    $keyJustFound = (string) $key;
                }
            }
        }

        $numParams = count($expectedParameters);
        $this->assertSame($numParams, $keyCount);
        $this->assertSame($numParams, $valueCount);

        // make sure each parameter was found
        foreach ($expectedParameters as $expectedParameter) {
            $this->assertTrue(
                $expectedParameter['found'],
                'Failed to find parameter with key ' . strval($expectedParameter['key'])
            );
        }
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The storeReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataSubscriptionRequired()
    {
        $this->request->setStoreReference(null);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $encryptedToken = $this->faker->encryptedUrlParameters();

        $this->setMockHttpResponse('HostedCheckoutPurchaseSuccess.txt', array(
            'ENCRYPTED_TOKEN' => $encryptedToken
        ));

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame('GET', $response->getRedirectMethod());
        $this->assertSame(
            'https://sandbox.bluesnap.com/buynow/checkout?storeId=' . $this->storeReference . '&enc=' . $encryptedToken,
            $response->getRedirectUrl()
        );
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('HostedCheckoutPurchaseFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'Parameter Encryption service failed due to problematic input. Missing Data Protection Key: '
                . 'please define it in the Console and try again.',
            $response->getMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
