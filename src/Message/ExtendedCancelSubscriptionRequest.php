<?php

namespace Omnipay\BlueSnap\Message;

use SimpleXMLElement;

/**
 * Cancel an existing subscription.
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
 *   // captured, you'll receive an IPN callback, at which point you could cancel the
 *   // subscription:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $cancelResponse = $gateway->cancelSubscription(array(
 *           'subscriptionReference' => $ipnCallback->getSubscriptionReference()
 *       ))->send();
 *
 *       if ($cancelResponse->isSuccessful()) {
 *           // do stuff
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedCancelSubscriptionRequest extends ExtendedUpdateSubscriptionRequest
{
    /**
     * @return SimpleXMLElement
     */
    public function getData()
    {
        $this->validate('subscriptionReference');

        $data = new SimpleXMLElement('<subscription />');
        $data->{'subscription-id'} = $this->getSubscriptionReference();
        $data->status = 'C';

        return $data;
    }
}
