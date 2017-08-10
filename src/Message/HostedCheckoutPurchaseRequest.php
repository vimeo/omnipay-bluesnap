<?php

namespace Omnipay\BlueSnap\Message;

use SimpleXMLElement;
use Omnipay\BlueSnap\Constants;

/**
 * Begin a BlueSnap Hosted Checkout purchase. A redirect URL will be returned to send the user to
 * BlueSnap's offsite checkout.
 *
 * This request builds the checkout URL with encrypted parameters. To use it, you must first set an
 * encryption key. Open the BlueSnap merchant console, click on "Payment Methods" under "Checkout
 * Page" on the left sidebar, and enter a key in the "Data Protection Key" field under "Optional
 * BuyNow Settings". Although BlueSnap lets you build a URL with unencrypted parameters, this driver
 * does not support that for security reasons.
 *
 * Parameters:
 * - storeReference (required): Your BlueSnap store ID number
 * - planReference (mostly required): The gateway's identifier for the plan (AKA Bluesnap contract) to be
 * purchased. BlueSnap often presents this in the form "sku######". "sku" should be left off when
 * using this driver. Be aware that BlueSnap differentiates between "products" and "plans": a
 * product can have multiple plans at different price points. This parameter is not technically
 * required since you could pass it as a storeParameter (see below). This would let you sell
 * multiple plans at once; however, multiple plans in one transaction are not fully
 * supported throughout this driver at this time.
 * - currency (optional): Currency to display the store in. If left out, the default currency will
 * be used. Be aware that BlueSnap's default checkout designs let the user select any currency they
 * want, but this feature can be removed.
 * - returnUrl (optional): The URL the shopper should be returned to after completing the checkout
 * on BlueSnap. You may need to contact BlueSnap support to enable this functionality for your
 * account. Alternatively, you can set this in the merchant console.
 * - storeParameters (optional): An array of additional parameters that you can set in the URL of
 * the checkout page. The array is of the form 'parameter_name' => 'value'. A list of parameters is
 * available at https://support.bluesnap.com/docs/buynow-parameters
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
 *       'currency' => 'JPY',
 *       'returnUrl' => 'https://www.example.com/thanks',
 *       'storeParameters' => array(
 *           'sku1234567priceamount' => '10.00', // optional price override of plan 1234567
 *           'language' => 'JAPANESE' // force store language to be Japanese
 *       )
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
 *   $decryptResponse = $gateway->decryptReturnUrl(array(
 *       'returnUrl' => $_SERVER['REQUEST_URI']
 *   ))->send();
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
class HostedCheckoutPurchaseRequest extends ExtendedAbstractRequest
{
    /**
     * @var string
     */
    protected static $RESPONSE_CLASS = '\Omnipay\BlueSnap\Message\HostedCheckoutPurchaseResponse';

    /**
     * @return SimpleXMLElement
     */
    public function getData()
    {
        $this->validate('storeReference');

        // planReference is not required to allow people to set different quantities or
        // multiple plans by specifying them in parameters. One day a nicer format for
        // specifying multiple plans could be provided.
        $planReference = $this->getPlanReference();
        $parameters = $this->getStoreParameters();
        $currency = $this->getCurrency();
        $returnUrl = $this->getReturnUrl();

        $data = new SimpleXMLElement('<param-encryption />');
        $parametersElement = $data->addChild('parameters');
        if (isset($planReference)) {
            $this->addParameter($parametersElement, 'sku' . (string) $planReference, '1');
        }
        if (isset($currency)) {
            $this->addParameter($parametersElement, 'currency', $currency);
        }
        if (isset($returnUrl)) {
            $this->addParameter($parametersElement, 'thankyou.backtosellerurl', urlencode($returnUrl));
        }
        if (isset($parameters)) {
            /**
             * @var \Omnipay\BlueSnap\UrlParameter
             */
            foreach ($parameters as $parameter) {
                $parameterElement = $parametersElement->addChild('parameter');
                $parameterElement->addChild('param-key', (string) $parameter->getKey());
                $parameterElement->addChild('param-value', (string) $parameter->getValue());
            }
        }

        return $data;
    }

    /**
     * Add a parameter to the parameters XML
     *
     * @param SimpleXMLElement $parameters
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function addParameter(SimpleXMLElement $parameters, $key, $value)
    {
        $element = $parameters->addChild('parameter');
        $element->addChild('param-key', $key);
        $element->addChild('param-value', $value);
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/tools/param-encryption';
    }

    /**
     * Returns the HTTP method to be used for this request
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return Constants::HTTP_METHOD_POST;
    }

    /**
     * Overriding to provide a more precise return type
     *
     * @return HostedCheckoutPurchaseResponse
     */
    public function send()
    {
        /**
         * @var HostedCheckoutPurchaseResponse
         */
        return parent::send();
    }
}
