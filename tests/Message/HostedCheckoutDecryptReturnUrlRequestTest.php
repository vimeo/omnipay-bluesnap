<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class HostedCheckoutDecryptReturnUrlRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var HostedCheckoutDecryptReturnUrlRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var string
     */
    protected $encryptedToken;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->encryptedToken = $this->faker->randomCharacters(
            DataFaker::ALPHABET_UPPER . DataFaker::ALPHABET_LOWER . DataFaker::DIGITS,
            $this->faker->intBetween(20, 60)
        );
        $this->returnUrl = $this->faker->url() . '?encParams=' . $this->encryptedToken;

        $this->request = new HostedCheckoutDecryptReturnUrlRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setReturnUrl($this->returnUrl);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/tools/param-decryption',
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

        $this->assertSame('param-decryption', $data->getName());
        $this->assertSame($this->encryptedToken, (string) $data->{'encrypted-token'});
    }

    /**
     * @return void
     */
    public function testGetDataQueryStringOnly()
    {
        $this->request->setReturnUrl('encParams=' . $this->encryptedToken);
        $data = $this->request->getData();

        $this->assertSame('param-decryption', $data->getName());
        $this->assertSame($this->encryptedToken, (string) $data->{'encrypted-token'});
    }

    /**
     * @return void
     */
    public function testGetDataEncryptedTokenOnly()
    {
        $this->request->setReturnUrl($this->encryptedToken);
        $data = $this->request->getData();

        $this->assertSame('param-decryption', $data->getName());
        $this->assertSame($this->encryptedToken, (string) $data->{'encrypted-token'});
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The returnUrl parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataReturnUrlRequired()
    {
        $this->request->setReturnUrl(null);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $param1 = $this->faker->randomCharacters(DataFaker::ALPHABET_LOWER, $this->faker->intBetween(3, 10));
        $value1 = $this->faker->customerReference();
        $param2 = $this->faker->randomCharacters(DataFaker::ALPHABET_LOWER, $this->faker->intBetween(3, 10));
        $value2 = $this->faker->transactionReference();

        $this->setMockHttpResponse('HostedCheckoutDecryptReturnUrlSuccess.txt', array(
            'PARAM_1' => $param1,
            'VALUE_1' => $value1,
            'PARAM_2' => $param2,
            'VALUE_2' => $value2
        ));

        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertEquals(
            array(
                $param1 => $value1,
                $param2 => $value2
            ),
            $response->getDecryptedParameters()
        );
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('HostedCheckoutDecryptReturnUrlFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        // @codingStandardsIgnoreStart
        $this->assertSame(
            'Parameter Decryption service failed due to problematic input. We recommend checking'
                . ' the parameter-encyption token input and try again or contact merchant support.',
            $response->getMessage()
        );
        // @codingStandardsIgnoreEnd
    }
}
