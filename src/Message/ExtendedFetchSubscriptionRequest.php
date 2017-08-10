<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Fetch a subscription.
 *
 * Parameters:
 * - subscriptionReference (required): The gateway's identifier for the subscription
 * to be canceled.
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
 *           echo 'Currency: ' . $subscriptionResponse->getCurrency() . PHP_EOL;
 *           echo 'Amount: ' . $subscriptionResponse->getAmount() . PHP_EOL;
 *           echo 'Next charge date: ' . $subscriptionResponse->getNextChargeDate()->format('Y-m-d') . PHP_EOL;
 *           echo 'Customer Reference: ' . $subscriptionResponse->getCustomerReference() . PHP_EOL;
 *           foreach ($subscriptionResponse->getSubscriptionCharges() as $charge) {
 *              echo 'Transaction reference ' . $charge->getTransactionReference() . PHP_EOL;
 *              echo 'Charge Amount ' . $charge->getAmount() . PHP_EOL;
 *              echo 'Charge currency ' . $charge->getCurrency() . PHP_EOL;
 *              echo 'Charge date ' . $charge->getDate()->format('Y-m-d') . PHP_EOL;
 *           }
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedFetchSubscriptionRequest extends ExtendedAbstractRequest
{
    /**
     * @return null
     */
    public function getData()
    {
        $this->validate('subscriptionReference');
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
            . '/subscriptions/'
            . strval($this->getSubscriptionReference())
            . '?fulldescription=true';
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
