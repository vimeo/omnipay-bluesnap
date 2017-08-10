<?php

namespace Omnipay\BlueSnap;

use Omnipay\Common\Helper;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Generic representation of a SubscriptionCharge object returned by a gateway.
 */
class SubscriptionCharge
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new subscription charge with the specified parameters
     *
     * @param array $parameters An array of parameters to set on the new object
     */
    public function __construct($parameters = array())
    {
        $this->initialize($parameters);
    }

    /**
     * Initialize this subscription charge with the specified parameters
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
     * @return static
     */
    protected function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * Get the transaction reference
     *
     * @return null|string
     */
    public function getTransactionReference()
    {
        /**
         * @var null|string
         */
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
     * Get the currency
     *
     * @return null|string
     */
    public function getCurrency()
    {
        /**
         * @var null|string
         */
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
        /**
         * @var null|string
         */
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
     * Get the customer reference
     *
     * @return null|string
     */
    public function getCustomerReference()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customerReference');
    }

    /**
     * Set the customer reference
     *
     * @param string $value
     * @return static
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    /**
     * Gets the date the subscription charge was created on
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        /**
         * @var \DateTime|null
         */
        return $this->getParameter('date') ?: null;
    }

    /**
     * Sets the date the subscription charge was created on
     *
     * @param \DateTime $value
     * @return static
     */
    public function setDate($value)
    {
        return $this->setParameter('date', $value);
    }

    /**
     * Gets subscription reference
     *
     * @return string|null
     */
    public function getSubscriptionReference()
    {
        /**
         * @var string|null
         */
        return $this->getParameter('subscriptionReference') ?: null;
    }

    /**
     * Sets the subscription reference
     *
     * @param string $value
     * @return static
     */
    public function setSubscriptionReference($value)
    {
        return $this->setParameter('subscriptionReference', $value);
    }
}
