<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Fetch a subscription charge. This is a charge that is made against a subscription,
 * and can provide you with the transaction reference so you can fetch the full
 * transaction details.
 *
 * Parameters:
 * - subscriptionChargeReference (required): The gateway's identifier for the subscription
 * charge to be fetched.
 * - subscriptionReference (required): The gateway's identifier for the subscription
 * the charge belongs to.
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
 *       $subscriptionResponse = $gateway->fetchSubscription(array(
 *           'subscriptionReference' => $ipnCallback->getSubscriptionReference()
 *       ))->send();
 *
 *       if ($subscriptionResponse->isSuccessful()) {
 *           $subscriptionChargeReferences = $subscriptionResponse->getSubscriptionChargeReferences();
 *
 *           // Now we can fetch each subscription charge
 *
 *           foreach ($subscriptionChargeReferences as $subscriptionChargeReference) {
 *               $subscriptionChargeResponse = $gateway->fetchSubscriptionCharge(array(
 *                   'subscriptionReference' => $ipnCallback->getSubscriptionReference(),
 *                   'subscriptionChargeReference' => $subscriptionChargeReference
 *               ))->send();
 *
 *               if ($subscriptionChargeResponse->isSuccessful()) {
 *                   echo 'Currency: ' . $subscriptionChargeResponse->getCurrency() . PHP_EOL;
 *                   echo 'Amount: ' . $subscriptionChargeResponse->getAmount() . PHP_EOL;
 *                   echo 'Date Created: ' . $subscriptionChargeResponse->getDateCreated()->format('Y-m-d') . PHP_EOL;
 *                   echo 'Transaction Reference: ' . $subscriptionChargeResponse->getTransactionReference() . PHP_EOL;
 *               } else {
 *                   // error handling
 *               }
 *           }
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedFetchSubscriptionChargeRequest extends ExtendedAbstractRequest
{
    /**
     * @return null
     */
    public function getData()
    {
        $this->validate('subscriptionReference');
        $this->validate('subscriptionChargeReference');
        return null;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/subscriptions/' . strval($this->getSubscriptionReference())
                                     . '/subscription-charges/' . strval($this->getSubscriptionChargeReference());
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
