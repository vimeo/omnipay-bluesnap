<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Fetch a BlueSnap customer.
 *
 * Parameters:
 * - customerReference (required): The gateway's identifier for the customer to be fetched
 *
 * <code>
 *   // Set up the gateway
 *   $gateway = \Omnipay\Omnipay::create('BlueSnap_Extended'); // BlueSnap_HostedCheckout works too
 *   $gateway->setUsername('your_username');
 *   $gateway->setPassword('y0ur_p4ssw0rd');
 *   $gateway->setTestMode(false);
 *
 *   // Customer gets created when they go through the offsite checkout
 *   // Alternatively, you could add support to this driver for the BlueSnap Create Shopper request
 *
 *   // Now we want to fetch the customer
 *   $response = $gateway->fetchCustomer(array(
 *       'customerId' => $customerResponse->getCustomerId()
 *   ))->send();
 *
 *   if ($response->isSuccessful()) {
 *       echo 'Customer Reference: " . $response->getCustomerReference()) . PHP_EOL;
 *       // Right now, this is all that can be grabbed from this response, so it's not
 *       // super useful. But more functionality could be added to the driver.
 *   }
 *
 * </code>
 */
class ExtendedFetchCustomerRequest extends ExtendedAbstractRequest
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
        return parent::getEndpoint() . '/shoppers/' . strval($this->getCustomerReference());
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
