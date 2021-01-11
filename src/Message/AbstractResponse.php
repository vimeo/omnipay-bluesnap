<?php


namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Transaction;
use Omnipay\Common\Message\RequestInterface;
use SimpleXMLElement;

class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse
{
    /**
     * HTTP request id
     *
     * @var string|null
     */
    protected $requestId;

    /**
     * HTTP response code
     *
     * @var string|null
     */
    protected $code;

    /**
     * @var string|SimpleXMLElement|array
     */
    protected $data;

    /**
     * @var Transaction|null
     */
    protected $transaction;

    /**
     * @param RequestInterface $request the initiating request.
     * @param mixed $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->transaction = null;
    }

    /**
     * Returns true if the request was successful, false otherwise
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return substr($this->getCode() ?: '', 0, 1) === '2';
    }

    /**
     * Get the ID from the HTTP request
     *
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set the ID from the HTTP request
     *
     * @param string|null $requestId
     * @return static
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * Get the HTTP response code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the HTTP response code
     *
     * @param string $code
     * @return static
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Overriding to provide more specific return type
     *
     * @return \Omnipay\BlueSnap\Message\AbstractRequest
     */
    public function getRequest()
    {
        /**
         * @var \Omnipay\BlueSnap\Message\AbstractRequest
         */
        return parent::getRequest();
    }

    /**
     * Get the error message from the response. Returns null if request was successful.
     *
     * Since this library works with both XML and JSON response data, this function checks for both types.
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch because we check the data typing before using.
     */
    public function getMessage()
    {
        if (!$this->isSuccessful()) {
            if ($this->data instanceof SimpleXMLElement && isset($this->data->message->description)) {
                return (string) $this->data->message->description;
            }
            if (is_string($this->data)) {
                return $this->data;
            }  // some error responses are plain text instead of XML
        }

        return null;
    }
}
