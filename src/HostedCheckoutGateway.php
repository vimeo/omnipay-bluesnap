<?php

namespace Omnipay\BlueSnap;

/**
 * BlueSnap BuyNow Hosted Checkout gateway
 *
 * This is the gateway for BlueSnap's BuyNow Hosted Checkout solution. In this solution, BlueSnap
 * manages the product catalog and the checkout page is on BlueSnap's website.  The customer is
 * redirected from your site to BlueSnap's checkout page, much like they would be for PayPal. From
 * there, they will be redirected back to your site and notification of transaction events will be
 * sent to you via IPN callbacks.
 *
 * The Hosted Checkout solution makes use of BlueSnap's Extended Payment API, not their standard
 * Payment API.
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
 *       'planReference' => '1234567',
 *       'currency' => 'JPY'
 *   ))->send();
 *
 *   if ($purchaseResponse->isSuccessful()) {
 *       echo "Redirecting to: " . $purchaseResponse->getRedirectUrl() . PHP_EOL;
 *       $purchaseResponse->redirect();
 *   } else {
 *       // error handling
 *   }
 *
 *   // Now the user is filling out info on BlueSnap's site. Then they get redirected back
 *   // to your site, where you can decrypt the parameters in the return URL if you wish:
 *
 *   $decryptResponse = $gateway->decryptReturnUrl($_SERVER['REQUEST_URI'])->send();
 *   if ($decryptResponse->isSuccessful()) {
 *       var_dump($decryptResponse->getDecryptedParameters());
 *   }
 *
 *   // Once the transaction has been captured, you'll receive an IPN callback, which you can
 *   // handle like so:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       echo 'Transaction reference: ' . $ipnCallback->getTransactionReference() . PHP_EOL;
 *       echo 'Amount: ' . $ipnCallback->getAmount() . PHP_EOL;
 *       echo 'Currency: ' . $ipnCallback->getCurrency() . PHP_EOL;
 *   } elseif ($ipnCallback->isCancellation()) {
 *       // etc.
 *   }
 * </code>
 */
class HostedCheckoutGateway extends ExtendedGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'BlueSnap Hosted Checkout';
    }

    /**
     * Initiate a purchase. This request returns a redirect response with the store URL.
     *
     * See Message\HostedCheckoutPurchaseRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\HostedCheckoutPurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\HostedCheckoutPurchaseRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\HostedCheckoutPurchaseRequest', $parameters);
    }

    /**
     * Decrypts an encrypted return URL.
     *
     * See Message\HostedCheckoutDecryptReturnUrlRequest for more details.
     *
     * @param array $parameters
     * @return \Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlRequest
     */
    public function decryptReturnUrl(array $parameters = array())
    {
        /**
         * @var \Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlRequest
         */
        return $this->createRequest('\Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlRequest', $parameters);
    }
}
