<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Fetch subscriptions by customer reference.
 *
 * If you want to fetch all subscriptions in a time range, see ReportingFetchSubscriptionsRequest.
 * If you want to fetch all canceled subscriptions, see ReportingFetchCanceledSubscriptionsRequest.
 * If you only want to fetch a single subscription, see ExtendedFetchSubscriptionRequest.
 *
 * Parameters:
 * - customerReference (required): The gateway's identifier for the customer to fetch subscriptions for
 *
 * <code>
 *   // Set up the gateway
 *   $gateway = \Omnipay\Omnipay::create('BlueSnap_HostedCheckout');
 *   $gateway->setUsername('your_username');
 *   $gateway->setPassword('y0ur_p4ssw0rd');
 *   $gateway->setTestMode(false);
 *
 *   // Start the purchase process
 *   $purchaseResponse = $gateway->purchase(array(
 *       'storeReference' => '12345',
 *       'planReference' => '1234567'
 *   ))->send();
 *
 *   if ($purchaseResponse->isSuccessful()) {
 *       echo "Redirecting to: " . $purchaseResponse->getRedirectUrl() . PHP_EOL;
 *       $purchaseResponse->redirect();
 *   } else {
 *       // error handling
 *   }
 *
 *   // Now the user is filling out info on BlueSnap's site. Once the transaction has been
 *   // captured, you'll receive an IPN callback, at which point you can fetch the subscription:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $subscriptionResponse = $gateway->fetchSubscriptions(array(
 *           'customerReference' => $ipnCallback->getCustomerReference()
 *       ))->send();
 *
 *       if ($subscriptionResponse->isSuccessful()) {
 *           foreach ($subscriptionResponse->getSubscriptions() as $subscription) {
 *              echo 'Subscription reference ' . $subscription->getSubscriptionReference() . PHP_EOL;
 *              echo 'Subscription Amount ' . $subscription->getAmount() . PHP_EOL;
 *              echo 'Subscription currency ' . $subscription->getCurrency() . PHP_EOL;
 *              echo 'Subscription status ' . $subscription->getStatus() . PHP_EOL;
 *           }
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedFetchSubscriptionsRequest extends ExtendedAbstractRequest
{
    /**
     * @return null
     */
    public function getData()
    {
        $this->validate('customerReference');
        return null;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint()
            . '/tools/shopper-subscriptions-retriever'
            . '?shopperid=' . strval($this->getCustomerReference())
            . '&fulldescription=true';
    }

    /**
     * Returns the HTTP method to be used for this request
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return Constants::HTTP_METHOD_GET;
    }
}
