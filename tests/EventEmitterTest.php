<?php

namespace Omnipay\BlueSnap;

use Exception;
use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;
use Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest;
use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\TestSubscriber;
use Omnipay\Common\Message\ResponseInterface;
use PaymentGatewayLogger\Event\Constants;
use PaymentGatewayLogger\Event\ErrorEvent;
use PaymentGatewayLogger\Event\RequestEvent;
use PaymentGatewayLogger\Event\ResponseEvent;
use PaymentGatewayLogger\Test\Framework\TestLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventEmitterTest extends TestCase
{
    /**
     * @var string
     */
    private $subscriptionReference;

    /**
     * @var TestSubscriber
     */
    private $testSubscriber;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->testSubscriber = new TestSubscriber('test_gateway', new TestLogger());
        $this->subscriptionReference = '123';

        parent::setUp();
    }

    /**
     * Ensures that 'Request' and 'Response' events are emitted when issuing a request.
     *
     * @psalm-suppress UndefinedMethod
     * @return void
     */
    public function testAuthorizeRequestSuccessfulResponseEmitted()
    {
        $this->setMockHttpResponse('ExtendedCancelSubscriptionSuccess.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference
        ));

        /** @var Client $customHttpClient */
        $customHttpClient = $this->getHttpClient();
        $eventDispatcher = $customHttpClient->getEventDispatcher();

        $eventDispatcher->addSubscriber($this->testSubscriber);

        $request = new ExtendedCancelSubscriptionRequest($customHttpClient, $this->getHttpRequest());
        $request->setSubscriptionReference($this->subscriptionReference);

        $class = $this;
        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_REQUEST_BEFORE_SEND,
                /** @return void */
                function (RequestEvent $event) use ($class) {
                    /** @var \Omnipay\Common\Message\RequestInterface $request */
                    $request = $event['request'];
                    $class->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest', $request);
                }
            );

        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_RESPONSE_SUCCESS,
                /** @return void */
                function (ResponseEvent $event) use ($class) {
                    /** @var ResponseInterface $response */
                    $response = $event['response'];
                    $class->assertInstanceOf('\Omnipay\BlueSnap\Message\Response', $response);
                }
            );

        $response = $request->send();
        $this->assertTrue($response->isSuccessful());

        $eventsDispatched = $this->testSubscriber->eventsDispatched;

        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_REQUEST_BEFORE_SEND]);
        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_RESPONSE_SUCCESS]);
        $this->assertArrayNotHasKey(Constants::OMNIPAY_REQUEST_ERROR, $eventsDispatched);
    }

    /**
     * Ensures that 'Request' and 'Error' events are emitted when issuing an improper request.
     *
     * @psalm-suppress UndefinedMethod
     * @return void
     */
    public function testAuthorizeRequestErrorEventEmitted()
    {
        $this->setMockHttpResponse('ExtendedCancelSubscriptionFailure.txt', array(
            'SUBSCRIPTION_REFERENCE' => $this->subscriptionReference
        ));

        /** @var Client $customHttpClient */
        $customHttpClient = $this->getMock(
            'Guzzle\Http\Client',
            array('getEventDispatcher', 'addSubscriber', 'createRequest')
        );
        $customHttpClient->method('getEventDispatcher')->willReturn(new EventDispatcher());

        // Mock the Guzzle request so that it throws an error
        $guzzle_request_mock = $this->getMock(
            'Guzzle\Http\Message\EntityEnclosingRequest',
            array('setHeader'),
            array(RequestInterface::POST, 'https://www.test.com')
        );
        $guzzle_request_mock->method('setHeader')->willReturnSelf();
        $customHttpClient->method('createRequest')->willReturn($guzzle_request_mock);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $customHttpClient->getEventDispatcher();
        $eventDispatcher->addSubscriber($this->testSubscriber);

        $request = new ExtendedCancelSubscriptionRequest($customHttpClient, $this->getHttpRequest());
        $request->setSubscriptionReference($this->subscriptionReference);

        $class = $this;
        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_REQUEST_BEFORE_SEND,
                /** @return void */
                function (RequestEvent $event) use ($class) {
                    /** @var \Omnipay\Common\Message\RequestInterface $request */
                    $request = $event['request'];
                    $class->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest', $request);
                }
            );

        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_REQUEST_ERROR,
                /** @return void */
                function (ErrorEvent $event) use ($class) {
                    /** @var \Error $error */
                    $error = $event['error'];
                    $class->assertInstanceOf('Guzzle\Common\Exception\RuntimeException', $error);
                }
            );

        $eventsDispatched  = array();
        $response = null;
        try {
            $response = $request->send();
            $this->fail();
        } catch (Exception $exception) {
            // We want to resume program execution to check events in $eventsDispatched.
            $eventsDispatched = $this->testSubscriber->eventsDispatched;
            $this->assertEquals('A client must be set on the request', $exception->getMessage());
        }

        $this->assertNull($response);

        // An exception will always be expected. Therefore $eventsDispatched should never be empty.
        $this->assertNotEmpty($eventsDispatched);
        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_REQUEST_BEFORE_SEND]);
        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_REQUEST_ERROR]);
        $this->assertArrayNotHasKey(Constants::OMNIPAY_RESPONSE_SUCCESS, $eventsDispatched);
    }
}
