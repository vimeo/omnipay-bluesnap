<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Response object for the HostedCheckoutPurchaseRequest. See that file for details.
 */
class HostedCheckoutPurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Returns true if the response is a redirect, false otherwise.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->isSuccessful();
    }

    /**
     * Returns the BuyNow Hosted Checkout store URL
     *
     * @return string
     * @psalm-suppress MixedPropertyFetch
     */
    public function getRedirectUrl()
    {
        /**
         * @var HostedCheckoutPurchaseRequest
         */
        $request = $this->getRequest();

        return 'https://'
            . (!$request->getTestMode() ? 'checkout' : 'sandbox')
            . '.bluesnap.com/buynow/checkout?'
            . 'storeId=' . strval($request->getStoreReference())
            . '&enc=' . $this->data->{'encrypted-token'};
    }

    /**
     * Returns the redirect method
     *
     * @return string
     */
    public function getRedirectMethod()
    {
        return Constants::HTTP_METHOD_GET;
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return array
     */
    public function getRedirectData()
    {
        return array();
    }
}
