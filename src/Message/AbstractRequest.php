<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use Exception;
use Guzzle\Http\Message\RequestInterface;
use Omnipay\BlueSnap\Constants;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\RuntimeException;
use PaymentGatewayLogger\Event\Constants as PaymentGatewayLoggerConstants;
use PaymentGatewayLogger\Event\ErrorEvent;
use PaymentGatewayLogger\Event\RequestEvent;
use PaymentGatewayLogger\Event\ResponseEvent;
use SimpleXMLElement;

/**
 * BlueSnap Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const API_VERSION = '2.0';
    const LIVE_ENDPOINT = 'https://ws.bluesnap.com';
    const TEST_ENDPOINT = 'https://sandbox.bluesnap.com';
    const XMLNS = 'http://ws.plimus.com';

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected static $RESPONSE_CLASS = '\Omnipay\BlueSnap\Message\Response';

    /**
     * Gets the username for making API calls
     *
     * @return string|null
     */
    public function getUsername()
    {
        return strval($this->getParameter('username')) ?: null;
    }

    /**
     * Sets the username for making API calls
     *
     * @param string $value
     * @return static
     */
    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * Gets the password for making API calls
     *
     * @return string|null
     */
    public function getPassword()
    {
        return strval($this->getParameter('password')) ?: null;
    }

    /**
     * Sets the password for making API calls
     *
     * @param string $value
     * @return static
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Gets the gateway's identifier for the transaction
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return strval($this->getParameter('transactionReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the transaction
     *
     * @param string $value
     * @return static
     */
    public function setTransactionReference($value)
    {
        return $this->setParameter('transactionReference', $value);
    }

    /**
     * Gets the gateway's identifier for the customer
     *
     * @return string|null
     */
    public function getCustomerReference()
    {
        return strval($this->getParameter('customerReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the customer
     *
     * @param string $value
     * @return static
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    /**
     * Gets the gateway's identifier for the subscription
     *
     * @return string|null
     */
    public function getSubscriptionReference()
    {
        return strval($this->getParameter('subscriptionReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the subscription
     *
     * @param string $value
     * @return static
     */
    public function setSubscriptionReference($value)
    {
        return $this->setParameter('subscriptionReference', $value);
    }

    /**
     * Gets the gateway's identifier for the subscription charge
     *
     * @return string|null
     */
    public function getSubscriptionChargeReference()
    {
        return strval($this->getParameter('subscriptionChargeReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the subscription charge
     *
     * @param string $value
     * @return static
     */
    public function setSubscriptionChargeReference($value)
    {
        return $this->setParameter('subscriptionChargeReference', $value);
    }

    /**
     * Gets the next charge date for the subscription
     *
     * @return \DateTime|null
     */
    public function getNextChargeDate()
    {
        /**
         * @var \DateTime|null
         */
        return $this->getParameter('nextChargeDate') ?: null;
    }

    /**
     * Sets the next charge date for the subscription
     * NOTE: BlueSnap does not let you specify times, only dates.
     * The DateTime provided MUST be in the Etc/GMT+8 time zone.
     *
     * @param \DateTime $value
     * @return static
     * @throws InvalidRequestException if the time zone is incorrect
     */
    public function setNextChargeDate($value)
    {
        $this->validateTimeZone($value);
        return $this->setParameter('nextChargeDate', $value);
    }

    /**
     * Gets the date/time for the beginning of the range of transactions or
     * subscriptions to be returned
     * The DateTime returned will be in the Etc/GMT+8 time zone.
     *
     * @return \DateTime|null
     */
    public function getStartTime()
    {
        /**
         * @var \DateTime|null
         */
        return $this->getParameter('startTime') ?: null;
    }

    /**
     * Sets the date/time for the beginning of the range of transactions or
     * subscriptions to be returned
     * NOTE: BlueSnap does not let you specify times, only dates.
     * The DateTime provided MUST be in the Etc/GMT+8 time zone.
     *
     * @param \DateTime $value
     * @return static
     * @throws InvalidRequestException if the time zone is incorrect
     */
    public function setStartTime($value)
    {
        $this->validateTimeZone($value);
        return $this->setParameter('startTime', $value);
    }

    /**
     * Gets the date/time for the end of the range of transactions or
     * subscriptions to be returned
     * The DateTime returned will be in the Etc/GMT+8 time zone.
     *
     * @return \DateTime|null
     */
    public function getEndTime()
    {
        /**
         * @var \DateTime|null
         */
        return $this->getParameter('endTime') ?: null;
    }

    /**
     * Sets the date/time for the end of the range of transactions or
     * subscriptions to be returned
     * NOTE: BlueSnap does not let you specify times, only dates.
     * The DateTime provided MUST be in the Etc/GMT+8 time zone.
     *
     * @param \DateTime $value
     * @return static
     * @throws InvalidRequestException if the time zone is incorrect
     */
    public function setEndTime($value)
    {
        $this->validateTimeZone($value);
        return $this->setParameter('endTime', $value);
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return ($this->getTestMode() === false ? self::LIVE_ENDPOINT : self::TEST_ENDPOINT) . '/services/2';
    }

    /**
     * Returns the HTTP method to be used for this request
     *
     * @return string
     */
    abstract public function getHttpMethod();

    /**
     * This method is only overriden to provide type hinting for static type checking
     * by Psalm.
     * This is necessary because Omnipay\Common\AbstractRequest::setParameter says it
     * returns AbstractRequest instead of static.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    protected function setParameter($key, $value)
    {
        /**
         * @var static
         */
        return parent::setParameter($key, $value);
    }

    /**
     * Throws an exception if the datetime is not in the Etc/GMT+8 time zone.
     *
     * @param DateTime $datetime
     * @return void
     * @throws InvalidRequestException
     */
    protected function validateTimeZone(DateTime $datetime)
    {
        if ($datetime->getTimeZone()->getName() !== Constants::BLUESNAP_TIME_ZONE) {
            throw new InvalidRequestException(
                'Dates must be provided in the ' . Constants::BLUESNAP_TIME_ZONE . ' time zone'
            );
        }
    }

    /**
     * Makes the request
     *
     * @param SimpleXMLElement|null $data
     * @return Response
     *
     * @throws RuntimeException if $data is invalid XML
     * @throws Exception if there is a problem when initiating the request.
     * @psalm-suppress TypeDoesNotContainType psalm bug with SimpleXMLElement: https://github.com/vimeo/psalm/issues/145
     */
    public function sendData($data)
    {
        if ($data instanceof SimpleXMLElement) {
            $data->addAttribute('xmlns', self::XMLNS);
            /**
             * @var string|false
             */
            $data = $data->asXML();

            if ($data === false) {
                throw new RuntimeException('Request data is not valid XML');
            }
        }

        $eventDispatcher = $this->httpClient->getEventDispatcher();

        // don't throw exceptions for errors
        $eventDispatcher->addListener(
            // @codingStandardsIgnoreStart
            'request.error',
            /**
             * @param array{response: \Guzzle\Http\Message\Response} $event
             * @return void
             */
            function ($event) {
                // @codingStandardsIgnoreEnd
                if ($event['response']->isClientError()) {
                    $event->stopPropagation();
                }
            }
        );

        /** @var RequestInterface $httpRequest */
        $httpRequest = $this->httpClient->createRequest(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            null,
            $data
        );

        $httpRequest
            ->setHeader(
                'Authorization',
                'Basic ' . base64_encode(($this->getUsername() ?: '') . ':' . ($this->getPassword() ?: ''))
            )
            ->setHeader('Content-Type', 'application/xml')
            ->setHeader('bluesnap-version', self::API_VERSION);

        $httpResponse = null;
        try {
            // Fire a request event before sending request.
            $eventDispatcher->dispatch(
                PaymentGatewayLoggerConstants::OMNIPAY_REQUEST_BEFORE_SEND,
                new RequestEvent($this)
            );

            $httpResponse = $httpRequest->send();
        } catch (Exception $e) {
            // Fire an error event if there was a problem with the request.
            $eventDispatcher->dispatch(
                PaymentGatewayLoggerConstants::OMNIPAY_REQUEST_ERROR,
                new ErrorEvent($e, $this)
            );

            throw $e;
        }

        // responses can be XML, JSON, or plain text depending on the request and whether it's successful
        try {
            if (strpos($httpResponse->getContentType(), 'json') !== false) {
                $responseData = $httpResponse->json();
            } else {
                $responseData = $httpResponse->xml();
            }
        } catch (\Guzzle\Common\Exception\RuntimeException $e) {
            $responseData = trim((string) $httpResponse->getBody(true));
        }

        /**
         * @var Response
         */
        $this->response = new static::$RESPONSE_CLASS($this, $responseData);

        $this->response->setCode((string) $httpResponse->getStatusCode());

        if ($httpResponse->hasHeader('Request-Id')) {
            $request_id_header = $httpResponse->getHeader('Request-Id');
            $this->response->setRequestId($request_id_header ? strval($request_id_header) : null);
        }

        // Log the successful request's response.
        $eventDispatcher->dispatch(
            PaymentGatewayLoggerConstants::OMNIPAY_RESPONSE_SUCCESS,
            new ResponseEvent($this->response)
        );

        return $this->response;
    }

    /**
     * Redefining to tell Psalm this function is variadic
     *
     * @return void
     * @psalm-variadic
     */
    public function validate()
    {
        call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }

    /**
     * Overriding to provide a more precise return type
     *
     * @return Response
     */
    public function send()
    {
        /**
         * @var Response
         */
        return parent::send();
    }
}
