<?php

namespace Omnipay\BlueSnap;

use Omnipay\Omnipay;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var Gateway
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
        $this->gateway = new Gateway($httpClient, $httpRequest);
        $this->gateway->setTestMode(true);
        $this->faker = new DataFaker();
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $this->assertSame('BlueSnap', $this->gateway->getName());
    }

    /**
     * @return void
     * @psalm-suppress MixedAssignment because that is exactly what this function allows
     */
    public function testCreation()
    {
        $gateway = Omnipay::create('BlueSnap_Extended');
        $this->assertInstanceOf('Omnipay\BlueSnap\ExtendedGateway', $gateway);
    }

    /**
     * @return void
     */
    public function testUsername()
    {
        $username = $this->faker->username();
        $this->assertSame($this->gateway, $this->gateway->setUsername($username));
        $this->assertSame($username, $this->gateway->getUsername());
    }

    /**
     * @return void
     */
    public function testPassword()
    {
        $password = $this->faker->password();
        $this->assertSame($this->gateway, $this->gateway->setPassword($password));
        $this->assertSame($password, $this->gateway->getPassword());
    }

    /**
     * @return void
     */
    public function testTestMode()
    {
        $testMode = $this->faker->bool();
        $this->assertSame($this->gateway, $this->gateway->setTestMode($testMode));
        $this->assertSame($testMode, $this->gateway->getTestMode());
    }

    /**
     * @return void
     */
    public function testFetchTransactions()
    {
        $startTime = $this->faker->datetime();
        $endTime = $this->faker->datetime();
        $request = $this->gateway->fetchTransactions(
            array(
                'startTime' => $startTime,
                'endTime' => $endTime
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\FetchTransactionsRequest', $request);
        $this->assertSame($startTime, $request->getStartTime());
        $this->assertSame($endTime, $request->getEndTime());
    }

    /**
     * @return void
     */
    public function testFetchSubscriptions()
    {
        $startTime = $this->faker->datetime();
        $endTime = $this->faker->datetime();
        $request = $this->gateway->fetchSubscriptions(
            array(
                'startTime' => $startTime,
                'endTime' => $endTime
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\FetchSubscriptionsRequest', $request);
        $this->assertSame($startTime, $request->getStartTime());
        $this->assertSame($endTime, $request->getEndTime());
    }

    /**
     * @return void
     */
    public function testFetchCanceledSubscriptions()
    {
        $startTime = $this->faker->datetime();
        $endTime = $this->faker->datetime();
        $request = $this->gateway->fetchCanceledSubscriptions(
            array(
                'startTime' => $startTime,
                'endTime' => $endTime
            )
        );
        $this->assertInstanceOf('Omnipay\BlueSnap\Message\FetchCanceledSubscriptionsRequest', $request);
        $this->assertSame($startTime, $request->getStartTime());
        $this->assertSame($endTime, $request->getEndTime());
    }

    /**
     * @return void
     */
    public function testParseIPNCallback()
    {
        $queryString = $this->faker->queryString();
        $result = $this->gateway->parseIPNCallback($queryString);

        $this->assertInstanceOf('Omnipay\BlueSnap\Message\IPNCallback', $result);
    }
}
