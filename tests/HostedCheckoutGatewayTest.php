<?php

namespace Omnipay\BlueSnap;

use Omnipay\Omnipay;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class HostedCheckoutGatewayTest extends GatewayTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var HostedCheckoutGateway
     */
    protected $gateway;

    /**
     * @return void
     */
    public function setUp()
    {
        /**
         * @var \Guzzle\Http\ClientInterface
         */
        $httpClient = $this->getHttpClient();
        /**
         * @var \Symfony\Component\HttpFoundation\Request
         */
        $httpRequest = $this->getHttpRequest();
        $this->gateway = new HostedCheckoutGateway($httpClient, $httpRequest);
        $this->gateway->setTestMode(true);
        $this->faker = new DataFaker();
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $this->assertSame('BlueSnap Hosted Checkout', $this->gateway->getName());
    }

    /**
     * @return void
     * @psalm-suppress MixedAssignment because that is exactly what this function allows
     */
    public function testCreation()
    {
        $gateway = Omnipay::create('BlueSnap_HostedCheckout');
        $this->assertInstanceOf('Omnipay\BlueSnap\HostedCheckoutGateway', $gateway);
    }

    /**
     * @return void
     */
    public function testPurchase()
    {
        $storeReference = $this->faker->storeReference();
        $planReference = $this->faker->planReference();
        $request = $this->gateway->purchase(
            array(
                'storeReference' => $storeReference,
                'planReference' => $planReference
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\HostedCheckoutPurchaseRequest', $request);
        $this->assertSame($storeReference, $request->getStoreReference());
        $this->assertSame($planReference, $request->getPlanReference());
    }

    /**
     * @return void
     */
    public function testDecryptReturnUrl()
    {
        $returnUrl = $this->faker->url();
        /**
         * @var \Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlRequest
         */
        $request = $this->gateway->decryptReturnUrl(
            array(
                'returnUrl' => $returnUrl
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlRequest', $request);
        $this->assertSame($returnUrl, $request->getReturnUrl());
    }
}
