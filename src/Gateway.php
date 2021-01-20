<?php

namespace Omnipay\BlueSnap;

use Omnipay\Common\AbstractGateway;
use Omnipay\BlueSnap\Message\IPNCallback;

/**
 * BlueSnap Payment API gateway
 *
 * This is the gateway for the standard BlueSnap Payment API. At this time, it is not fully
 * implemented. This driver currently only supports BlueSnap's BuyNow Hosted Checkout solution. See
 * the HostedCheckoutGateway.
 */
class Gateway extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'BlueSnap';
    }

    /**
     * Get the gateway parameters.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'username' => '',
            'password' => '',
            'testMode' => false,
        );
    }

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
     * Fetch all transactions in a time range.
     *
     * See Message\ReportingFetchTransactionsRequest for more details.
     *
     * @param array $parameters
     *
     * @return \Omnipay\BlueSnap\Message\ReportingFetchTransactionsRequest
     */
    public function fetchTransactions(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ReportingFetchTransactionsRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ReportingFetchTransactionsRequest', $parameters);
    }

    /**
     * Fetch all active subscriptions in a time range.
     *
     * See Message\ReportingFetchSubscriptionsRequest for more details.
     *
     * @param array $parameters
     *
     * @return \Omnipay\BlueSnap\Message\ReportingFetchSubscriptionsRequest
     */
    public function fetchSubscriptions(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ReportingFetchSubscriptionsRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ReportingFetchSubscriptionsRequest', $parameters);
    }

    /**
     * Fetch all canceled subscriptions in a time range.
     *
     * See Message\ReportingFetchCanceledSubscriptionsRequest for more details.
     *
     * @param array $parameters
     *
     * @return \Omnipay\BlueSnap\Message\ReportingFetchCanceledSubscriptionsRequest
     */
    public function fetchCanceledSubscriptions(array $parameters = array())
    {
        $class = '\Omnipay\BlueSnap\Message\ReportingFetchCanceledSubscriptionsRequest';

        /**
         * @var \Omnipay\BlueSnap\Message\ReportingFetchCanceledSubscriptionsRequest
         */
        return $this->createRequest($class, $parameters);
    }

    /**
     * Parse an IPN callback URL for easier handling. Note that this does NOT
     * make an API request.
     *
     * See Message\IPNCallback for more details.
     *
     * @param string|array<string, string> $url
     * @return \Omnipay\BlueSnap\Message\IPNCallback
     */
    public function parseIPNCallback($url)
    {
        return new IPNCallback($url);
    }
}
