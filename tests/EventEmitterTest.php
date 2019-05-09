<?php

namespace Omnipay\BlueSnap;

use Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\BlueSnap\Test\Framework\TestCase;
use Omnipay\BlueSnap\Test\Framework\TestSubscriber;
use PaymentGatewayLogger\Event\Constants;
use PaymentGatewayLogger\Event\RequestEvent;
use PaymentGatewayLogger\Event\ResponseEvent;
use PaymentGatewayLogger\Test\Framework\TestLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventEmitterTest extends TestCase
{
    protected $customHttpClient;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TestSubscriber
     */
    protected $testSubscriber;

    /**
     * @var string
     */
    private $subscriptionReference;

    /**
     * Ensures that 'Request' and 'Response' events are emitted when issuing a request.
     *
     * @return void
     */
    public function testAuthorizeRequestSuccessfulResponseEmitted()
    {
        $faker = new DataFaker();
        $subscriptionReference = $faker->subscriptionReference();

        $this->setMockHttpResponse('ExtendedCancelSubscriptionSuccess.txt', array(
            'SUBSCRIPTION_REFERENCE' => $subscriptionReference
        ));

        $testSubscriber = new TestSubscriber($faker->name(), new TestLogger());
        $customHttpClient = $this->getHttpClient();
        $eventDispatcher = $customHttpClient->getEventDispatcher();

        $eventDispatcher->addSubscriber($testSubscriber);

        $request = new ExtendedCancelSubscriptionRequest($customHttpClient, $this->getHttpRequest());
        $request->setSubscriptionReference($subscriptionReference);

        $class = $this;
        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_REQUEST_BEFORE_SEND,
                /** @return void */
                function (RequestEvent $event) use ($class) {
                    $request = $event['request'];
                     $class->assertInstanceOf('Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest', $request);
                }
            );

        $eventDispatcher
            ->addListener(
                Constants::OMNIPAY_RESPONSE_SUCCESS,
                /** @return void */
                function (ResponseEvent $event) use ($class) {
                    $response = $event['response'];
                    $class->assertInstanceOf('\Omnipay\BlueSnap\Message\Response', $response);
                }
            );

        $response = $request->send();
        $this->assertTrue($response->isSuccessful());

        $eventsDispatched = $testSubscriber->eventsDispatched;

        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_REQUEST_BEFORE_SEND]);
        $this->assertEquals(1, $eventsDispatched[Constants::OMNIPAY_RESPONSE_SUCCESS]);
        $this->assertArrayNotHasKey(Constants::OMNIPAY_REQUEST_ERROR, $eventsDispatched);
    }
}
