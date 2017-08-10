<?php

namespace Omnipay\BlueSnap;

/**
 * BlueSnap Extended Payment API gateway
 *
 * This is the gateway for the BlueSnap Extended Payment API. At this time, enough of it is
 * implemented to power BlueSnap's Hosted Checkout solution (see HostedCheckoutGateway). Other uses
 * of the Extended Payment API are not fully supported.
 */
class ExtendedGateway extends Gateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'BlueSnap Extended';
    }

    /**
     * Fetch a BlueSnap customer object.
     *
     * See Message\ExtendedFetchCustomerRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedFetchCustomerRequest
     */
    public function fetchCustomer(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedFetchCustomerRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedFetchCustomerRequest', $parameters);
    }

    /**
     * Fetch a BlueSnap transaction object.
     * Their extended payment API calls transactions "orders"
     *
     * See Message\ExtendedFetchTransactionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedFetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedFetchTransactionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedFetchTransactionRequest', $parameters);
    }

    /**
     * Fetch a BlueSnap subscription object.
     *
     * See Message\ExtendedFetchSubscriptionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionRequest
     */
    public function fetchSubscription(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionRequest', $parameters);
    }

    /**
     * Fetch BlueSnap subscription objects by customer or in a time range.
     *
     * See Message\ExtendedFetchSubscriptionsRequest for more details on fetching
     * by customer. See Message\FetchSubscriptionsRequest for more details on
     * fetching by time range. The correct request will be made depending on the
     * parameters passed.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\AbstractRequest
     */
    public function fetchSubscriptions(array $parameters = array())
    {
        if (isset($parameters['customerReference'])) {
            /**
             * @var \Omnipay\BlueSnap\Message\AbstractRequest
             */
            return $this->createRequest(
                '\Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionsRequest',
                $parameters
            );
        }
        return parent::fetchSubscriptions($parameters);
    }

    /**
     * Fetch a BlueSnap subscription charge object.
     *
     * See Message\ExtendedFetchSubscriptionChargeRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionChargeRequest
     */
    public function fetchSubscriptionCharge(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionChargeRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedFetchSubscriptionChargeRequest', $parameters);
    }

    /**
     * Update a subscription
     *
     * See Message\ExtendedUpdateSubscriptionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedUpdateSubscriptionRequest
     */
    public function updateSubscription(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedUpdateSubscriptionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedUpdateSubscriptionRequest', $parameters);
    }

    /**
     * Cancel a subscription
     *
     * See Message\ExtendedCancelSubscriptionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest
     */
    public function cancelSubscription(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedCancelSubscriptionRequest', $parameters);
    }

    /**
     * Reactivate a subscription that has previously been canceled
     *
     * See Message\ExtendedReactivateSubscriptionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedReactivateSubscriptionRequest
     */
    public function reactivateSubscription(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedReactivateSubscriptionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedReactivateSubscriptionRequest', $parameters);
    }

    /**
     * Make a test charge against a subscription. This only works in test mode.
     *
     * See Message\ExtendedTestChargeSubscriptionRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedTestChargeSubscriptionRequest
     */
    public function testChargeSubscription(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedTestChargeSubscriptionRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedTestChargeSubscriptionRequest', $parameters);
    }

    /**
     * Refund a transaction
     *
     * See Message\ExtendedRefundRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\ExtendedRefundRequest
     */
    public function refund(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\ExtendedRefundRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\ExtendedRefundRequest', $parameters);
    }
}
