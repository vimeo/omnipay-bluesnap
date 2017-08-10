<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Make a test charge against a subscription.
 *
 * This only works in test mode (on the BlueSnap sandbox environment). You will need BlueSnap to
 * enable this functionality for you.
 *
 * Parameters:
 * - subscriptionReference (required): The gateway's identifier for the subscription to be charged.
 *
 * <code>
 *   // Set up the gateway
 *   $gateway = \Omnipay\Omnipay::create('BlueSnap_HostedCheckout');
 *   $gateway->setUsername('your_username');
 *   $gateway->setPassword('y0ur_p4ssw0rd');
 *   $gateway->setTestMode(true); // test mode must be true
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
 *   // captured, you'll receive an IPN callback, at which point you can charge the subscription:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $chargeResponse = $gateway->testChargeSubscription(array(
 *           'subscriptionReference' => $ipnCallback->getSubscriptionReference()
 *       ))->send();
 *
 *       if ($chargeResponse->isSuccessful()) {
 *           // do stuff
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedTestChargeSubscriptionRequest extends ExtendedAbstractRequest
{
    /**
     * @return null
     */
    public function getData()
    {
        $this->validate('subscriptionReference');
        if (!$this->getTestMode()) {
            throw new InvalidRequestException(
                'You cannot make a test subscription charge if you\'re not in test mode'
            );
        }
        return null;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/subscriptions/'
                                     . strval($this->getSubscriptionReference()) . '/run-specific';
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
