<?php

namespace Omnipay\BlueSnap;

use Omnipay\Common\Helper;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Generic representation of a transaction object returned by a gateway.
 */
class Transaction
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new transaction with the specified parameters
     *
     * @param array $parameters An array of parameters to set on the new object
     */
    public function __construct($parameters = array())
    {
        $this->initialize($parameters);
    }

    /**
     * Initialize this transaction with the specified parameters
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
     * Gets the date the transaction was created on
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
     * Sets the date the transaction was created on
     *
     * @param \DateTime $value
     * @return static
     * @throws InvalidRequestException if the time zone is incorrect
     */
    public function setDate($value)
    {
        return $this->setParameter('date', $value);
    }

    /**
     * Gets transaction status
     *
     * @return string|null
     */
    public function getStatus()
    {
        /**
         * @var string|null
         */
        return $this->getParameter('status') ?: null;
    }

    /**
     * Sets the transaction status
     *
     * @param string $value
     * @return static
     */
    public function setStatus($value)
    {
        return $this->setParameter('status', $value);
    }

    /**
     * Get custom parameter #1
     *
     * @return null|string
     */
    public function getCustomParameter1()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter1');
    }

    /**
     * Set custom parameter #1
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter1($value)
    {
        return $this->setParameter('customParameter1', $value);
    }

    /**
     * Get custom parameter #2
     *
     * @return null|string
     */
    public function getCustomParameter2()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter2');
    }

    /**
     * Set custom parameter #2
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter2($value)
    {
        return $this->setParameter('customParameter2', $value);
    }

    /**
     * Get custom parameter #3
     *
     * @return null|string
     */
    public function getCustomParameter3()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter3');
    }

    /**
     * Set custom parameter #3
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter3($value)
    {
        return $this->setParameter('customParameter3', $value);
    }

    /**
     * Get custom parameter #4
     *
     * @return null|string
     */
    public function getCustomParameter4()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter4');
    }

    /**
     * Set custom parameter #4
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter4($value)
    {
        return $this->setParameter('customParameter4', $value);
    }

    /**
     * Get custom parameter #5
     *
     * @return null|string
     */
    public function getCustomParameter5()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter5');
    }

    /**
     * Set custom parameter #5
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter5($value)
    {
        return $this->setParameter('customParameter5', $value);
    }

    /**
     * Get custom parameter #6
     *
     * @return null|string
     */
    public function getCustomParameter6()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter6');
    }

    /**
     * Set custom parameter #6
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter6($value)
    {
        return $this->setParameter('customParameter6', $value);
    }

    /**
     * Get custom parameter #7
     *
     * @return null|string
     */
    public function getCustomParameter7()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter7');
    }

    /**
     * Set custom parameter #7
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter7($value)
    {
        return $this->setParameter('customParameter7', $value);
    }

    /**
     * Get custom parameter #8
     *
     * @return null|string
     */
    public function getCustomParameter8()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter8');
    }

    /**
     * Set custom parameter #8
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter8($value)
    {
        return $this->setParameter('customParameter8', $value);
    }

    /**
     * Get custom parameter #9
     *
     * @return null|string
     */
    public function getCustomParameter9()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter9');
    }

    /**
     * Set custom parameter #9
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter9($value)
    {
        return $this->setParameter('customParameter9', $value);
    }

    /**
     * Get custom parameter #10
     *
     * @return null|string
     */
    public function getCustomParameter10()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('customParameter10');
    }

    /**
     * Set custom parameter #10
     *
     * @param string $value
     * @return static
     */
    public function setCustomParameter10($value)
    {
        return $this->setParameter('customParameter10', $value);
    }
}
