<?php
/**
 * omnipay-bluesnap
 *
 * @version    1
 */

namespace Omnipay\BlueSnap\Test\Framework;


use Guzzle\Common\Event;
use PaymentGatewayLogger\Event\Constants;
use PaymentGatewayLogger\Event\Subscriber\OmnipayGatewayRequestSubscriber;

class TestSubscriber extends OmnipayGatewayRequestSubscriber
{
    const PRIORITY = 0;

    /** @var array */
    public $eventsDispatched = array();

    /**
     * Triggers a log write before a request is sent.
     *
     * The event will be converted to an array before being logged. It will contain the following properties:
     *     array(
     *         'request' => \Omnipay\Common\Message\AbstractRequest
     *     )
     * @param Event $event
     * @return void
     */
    public function onOmnipayRequestBeforeSend(Event $event)
    {
        $this->incrementEventCount(Constants::OMNIPAY_REQUEST_BEFORE_SEND);
    }

    /**
     * Triggers a log write when a request completes.
     *
     * The event will be converted to an array before being logged. It will contain the following properties:
     *     array(
     *         'response' => \Omnipay\Common\Message\AbstractResponse
     *     )
     * @param Event $event
     * @return void
     */
    public function onOmnipayResponseSuccess(Event $event)
    {
        $this->incrementEventCount(Constants::OMNIPAY_RESPONSE_SUCCESS);
    }

    /**
     * Triggers a log write when a request fails.
     *
     * The event will be converted to an array before being logged. It will contain the following properties:
     *     array(
     *         'error' => Exception
     *     )
     * @param Event $event
     * @return void
     */
    public function onOmnipayRequestError(Event $event)
    {
        $this->incrementEventCount(Constants::OMNIPAY_REQUEST_ERROR);
    }

    /**
     * Increments event occurrences.
     * @param string $event_name
     * @return void
     */
    protected function incrementEventCount($event_name)
    {
        if (isset($this->eventsDispatched[$event_name])) {
            $this->eventsDispatched[$event_name]++;
        } else {
            $this->eventsDispatched[$event_name] = 1;
        }
    }
}
