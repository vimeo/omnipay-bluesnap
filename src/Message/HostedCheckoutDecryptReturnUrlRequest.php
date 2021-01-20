<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;
use SimpleXMLElement;

/**
 * Decrypts an encrypted return URL.
 *
 * The return URL is the URL that the user is redirected back to after completing their purchase
 * on BlueSnap's website. You can configure this and set custom parameters in the merchant console.
 * BlueSnap calls this a "callback" URL.
 *
 * BlueSnap does not encrypt return URLs by default, but you can ask them to enable it for your
 * account, which we highly recommend.
 *
 * Parameters:
 * - returnUrl (required): The return URL. This can be a full URL, just the query string, or just
 * the encrypted parameter value.
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
 *   // Now the user is filling out info on BlueSnap's site. Then they get redirected back
 *   // to your site, where you can decrypt the parameters in the return URL:
 *
 *   $decryptResponse = $gateway->decryptReturnUrl(array(
 *       'returnUrl' => $_SERVER['REQUEST_URI']
 *   ))->send();
 *   if ($decryptResponse->isSuccessful()) {
 *       var_dump($decryptResponse->getDecryptedParameters());
 *   }
 * </code>
 */
class HostedCheckoutDecryptReturnUrlRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected static $RESPONSE_CLASS = '\Omnipay\BlueSnap\Message\HostedCheckoutDecryptReturnUrlResponse';

    /**
     * @return SimpleXMLElement
     * @psalm-suppress PossiblyInvalidArrayAccess because the existence of the key is checked first before using it.
     */
    public function getData()
    {
        $this->validate('returnUrl');
        $returnUrl = $this->getReturnUrl() ?: '';

        // if returnUrl is a full URL, find the query string, otherwise assume it's all a query string
        /**
         * @var array<string, string|int>|false
         */
        $urlParts = parse_url($returnUrl);
        $queryString = isset($urlParts['query']) ? (string) $urlParts['query'] : $returnUrl;

        // find the encrypted parameters in the query string;
        // if not there, assume the whole thing is the encrypted parameters
        parse_str($queryString, $queryParams);
        /**
         * @var string
         */
        $encryptedToken = isset($queryParams['encParams']) ? $queryParams['encParams'] : $returnUrl;

        $data = new SimpleXMLElement('<param-decryption />');
        $data->{'encrypted-token'} = $encryptedToken;

        return $data;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/tools/param-decryption';
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
     * @return HostedCheckoutDecryptReturnUrlResponse
     */
    public function send()
    {
        /**
         * @var HostedCheckoutDecryptReturnUrlResponse
         */
        return parent::send();
    }
}
