<?php


namespace Omnipay\BlueSnap\Message;

use DateTime;
use Omnipay\BlueSnap\Transaction;
use Omnipay\Common\Helper;
use Omnipay\Common\ItemBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * Generic representation of a refund object returned by a gateway.
 * Hopefully this can be added to omnipay-common one day.
 */
class Refund
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new refund with the specified parameters
     *
     * @param array $parameters An array of parameters to set on the new object
     */
    public function __construct($parameters = array())
    {
        $this->initialize($parameters);
    }

    /**
     * Initialize this refund with the specified parameters
     *
     * @param array $parameters An array of parameters to set on this object
     * @return static
     */
    public function initialize($parameters = array())
    {
        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->all();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Refund
     */
    protected function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * Get the refund id.
     *
     * This value will match refundReference. In other gateways the refund id would contain the client-specified
     * refund id but BlueSnap does not support this.
     *
     * @return null|string
     */
    public function getRefundId()
    {
        return $this->getParameter('refundId');
    }

    /**
     * Set the refund id
     *
     * @param string $value
     * @return static
     */
    public function setRefundId($value)
    {
        return $this->setParameter('refundId', $value);
    }

    /**
     * Get the refund reference
     *
     * @return null|string
     */
    public function getRefundReference()
    {
        return $this->getParameter('refundReference');
    }

    /**
     * Set the refund reference
     *
     * @param string $value
     * @return static
     */
    public function setRefundReference($value)
    {
        return $this->setParameter('refundReference', $value);
    }

    /**
     * Get the refund id
     *
     * @return null|string
     * @deprecated see getRefundId
     */
    public function getId()
    {
        return $this->getRefundId();
    }

    /**
     * Set the refund id
     *
     * @param string $value
     * @return static
     * @deprecated see setRefundId
     */
    public function setId($value)
    {
        return $this->setRefundId($value);
    }

    /**
     * Get the refund reference
     *
     * @return null|string
     * @deprecated see getRefundReference
     */
    public function getReference()
    {
        return $this->getRefundReference();
    }

    /**
     * Set the refund reference
     *
     * @param string $value
     * @return static
     * @deprecated see setRefundReference
     */
    public function setReference($value)
    {
        return $this->setRefundReference($value);
    }

    /**
     * Get the currency
     *
     * @return null|string
     */
    public function getCurrency()
    {
        return $this->getParameter('currency');
    }

    /**
     * Set the currency
     *
     * @param string $value
     * @return static
     */
    public function setCurrency($value)
    {
        return $this->setParameter('currency', $value);
    }

    /**
     * Get the monetary amount
     *
     * @return null|string
     */
    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    /**
     * Set the monetary amount
     *
     * @param string $value
     * @return static
     */
    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }

    /**
     * Get the reason
     *
     * @return null|string
     */
    public function getReason()
    {
        return $this->getParameter('reason');
    }

    /**
     * Set the reason
     *
     * @param string $value
     * @return static
     */
    public function setReason($value)
    {
        return $this->setParameter('reason', $value);
    }

    /**
     * Get the reason
     *
     * @return null|string
     * @deprecated in favor of getReason
     */
    public function getNote()
    {
        return $this->getReason();
    }

    /**
     * Set the reason
     *
     * @param string $value
     * @return static
     * @deprecated in favor of setReason
     */
    public function setNote($value)
    {
        return $this->setReason($value);
    }

    /**
     * Get the time
     *
     * @return DateTime|null
     */
    public function getTime()
    {
        return $this->getParameter('time');
    }

    /**
     * Set the time
     *
     * @param DateTime|null $value
     * @return static
     */
    public function setTime($value)
    {
        return $this->setParameter('time', $value);
    }

    /**
     * Get the transaction
     *
     * @return null|Transaction
     */
    public function getTransaction()
    {
        return $this->getParameter('transaction');
    }

    /**
     * Set the transaction
     *
     * @param Transaction $value
     * @return static
     */
    public function setTransaction($value)
    {
        return $this->setParameter('transaction', $value);
    }

    /**
     * Get the transaction id
     *
     * @return null|string
     */
    public function getTransactionId()
    {
        return $this->getParameter('transactionId');
    }

    /**
     * Set the transaction id
     *
     * @param string $value
     * @return static
     */
    public function setTransactionId($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    /**
     * Get the transaction reference
     *
     * @return null|string
     */
    public function getTransactionReference()
    {
        return $this->getParameter('transactionReference');
    }

    /**
     * Set the transaction reference
     *
     * @param string $value
     * @return static
     */
    public function setTransactionReference($value)
    {
        return $this->setParameter('transactionReference', $value);
    }

    /**
     * Get the items
     *
     * @return null|ItemBag
     */
    public function getItems()
    {
        return $this->getParameter('items');
    }

    /**
     * Set the items
     *
     * @param array $items
     * @return static
     */
    public function setItems($items)
    {
        return $this->setParameter('items', $items);
    }

    /**
     * A list of attributes
     *
     * @return AttributeBag|null
     */
    public function getAttributes()
    {
        return $this->getParameter('attributes');
    }

    /**
     * Set the attributes in this order
     *
     * @param array $attributes
     * @return static
     */
    public function setAttributes($attributes)
    {
        return $this->setParameter('attributes', $attributes);
    }
}
