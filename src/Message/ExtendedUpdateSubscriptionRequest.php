<?php

namespace Omnipay\BlueSnap\Message;

use SimpleXMLElement;
use Omnipay\BlueSnap\Constants;

/**
 * Update an existing subscription.
 *
 * If you get an error message stating that the customer hasn't given permission
 * to be charged again, contact BlueSnap support.
 *
 * To cancel or reactivate a subscription, see ExtendedCancelSubscriptionRequest and
 * ExtendedReactivateSubscriptionRequest.
 *
 * Parameters:
 * - subscriptionReference (required): The gateway's identifier for the subscription
 * to be updated.
 * - planReference (optional): A new plan (bluesnap contract) for the subscription.
 * - currency (optional): A new currency for the subscription.
 * - amount (optional): A new monetary amount for the subscription.
 * - nextChargeDate (optional): A new next charge date for the subscription. The date
 * must be in the 'Etc/GMT+8' time zone. 'Etc/GMT+8' meants GMT-8, also known as
 * PST. BlueSnap does not observe daylight savings. BlueSnap also does not accept
 * hours, minutes, or seconds. Dates only.
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
 *   // captured, you'll receive an IPN callback, at which point you could update the
 *   // subscription:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $updateResponse = $gateway->updateSubscription(array(
 *           'subscriptionReference' => $ipnCallback->getSubscriptionReference(),
 *           'planReference' => '1234567',
 *           'currency' => 'GBP',
 *           'amount' => '123.12',
 *           'nextChargeDate' => new DateTime('2018-09-01', new DateTimeZone('Etc/GMT+8'))
 *       ))->send();
 *
 *       if ($updateResponse->isSuccessful()) {
 *           // do stuff
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedUpdateSubscriptionRequest extends ExtendedAbstractRequest
{
    /**
     * @return SimpleXMLElement
     */
    public function getData()
    {
        $this->validate('subscriptionReference');

        $data = new SimpleXMLElement('<subscription />');

        $data->{'subscription-id'} = $this->getSubscriptionReference();

        $newPrice = $this->getAmount();
        $newCurrency = $this->getCurrency();
        if (isset($newPrice)) {
            $data->{'override-recurring-charge'}->amount = $newPrice;
        }
        if (isset($newCurrency)) {
            $data->{'override-recurring-charge'}->currency = $newCurrency;
        }

        $newNextChargeDate = $this->getNextChargeDate();
        if (isset($newNextChargeDate)) {
            $data->{'next-charge-date'} = $newNextChargeDate->format('d-M-y');
        }

        $planReference = $this->getPlanReference();
        if (isset($planReference)) {
            $data->{'underlying-sku-id'} = $planReference;
        }

        return $data;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/subscriptions/' . strval($this->getSubscriptionReference());
    }

    /**
     * Returns the HTTP method to be used for this request
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return Constants::HTTP_METHOD_PUT;
    }
}
