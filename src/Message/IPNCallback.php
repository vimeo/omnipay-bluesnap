<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;
use DateTime;
use DateTimeZone;

/**
 * This object parses an IPN callback. It does not make an API request, it is just to
 * make working with IPNs easier.
 *
 * It takes one parameter, which can be the full IPN callback URL or just the query string.
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
 *   // captured, you'll receive an IPN callback.
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   // OR: $ipnCallback = $gateway->parseIPNCallback($_POST);
 *   if ($ipnCallback->isCharge()) {
 *       echo 'Transaction reference: ' . $ipnCallback->getTransactionReference() . PHP_EOL;
 *       echo 'Subscription reference: ' . $ipnCallback->getSubscriptionReference() . PHP_EOL;
 *       echo 'Plan reference: ' . $ipnCallback->getPlanReference() . PHP_EOL;
 *       echo 'Customer reference: ' . $ipnCallback->getCustomerReference() . PHP_EOL;
 *       echo 'Amount: ' . $ipnCallback->getAmount() . PHP_EOL;
 *       echo 'Currency: ' . $ipnCallback->getCurrency() . PHP_EOL;
 *       echo 'Date: ' . $ipnCallback->getDate()->format('Y-m-d') . PHP_EOL;
 *       echo 'Misc. parameter: ' . $ipnCallback->getParameter('paramName') . PHP_EOL;
 *   } elseif ($ipnCallback->isCancellation()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isCancellationRequest()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isChargeback()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isSubscriptionCharge()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isRefund()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isChargeFailure()) {
 *       // do stuff
 *   } elseif ($ipnCallback->isSubscriptionChargeFailure()) {
 *       // do stuff
 *   }
 * </code>
 */
class IPNCallback
{
    /**
     * @var array<string, string>
     */
    protected $queryParams;

    /**
     * Build and parse the IPN callback.
     * $ipn can be a full URL, just the query string, or the value of the $_POST variable.
     *
     * @param string|array<string, string> $ipn
     * @psalm-suppress PossiblyInvalidArrayAccess because the existence of the key is checked first before using it.
     */
    public function __construct($ipn)
    {
        if (is_array($ipn)) {
            $this->queryParams = $ipn;
            return;
        }

        /**
         * @var array<string, string|int>|false
         */
        $urlParts = parse_url($ipn);
        $queryString = isset($urlParts['query']) ? (string) $urlParts['query'] : $ipn;

        parse_str($queryString, $this->queryParams);
    }

    /**
     * Is this a CHARGE IPN? Charge IPNs are fired when a transaction is successfully
     * captured. For subscription charges, see isSubscriptionCharge.
     *
     * @return bool
     */
    public function isCharge()
    {
        return $this->getIPNType() === 'CHARGE';
    }

    /**
     * Is this a CANCELLATION IPN? Cancellation IPNs are fired when a recurring charge
     * is cancelled.
     *
     * @return bool
     */
    public function isCancellation()
    {
        return $this->getIPNType() === 'CANCELLATION';
    }

    /**
     * Is this a CANCEL_ON_RENEWAL IPN? This IPN is fired when a user cancels or opts out
     * of their subscription. The subscription will be automatically canceled at the end of
     * the billing period.
     *
     * @return bool
     */
    public function isCancellationRequest()
    {
        return $this->getIPNType() === 'CANCEL_ON_RENEWAL';
    }

    /**
     * Is this a CHARGEBACK IPN? Chargeback IPNs are fired when a transaction has a chargeback.
     *
     * @return bool
     */
    public function isChargeback()
    {
        return $this->getIPNType() === 'CHARGEBACK';
    }

    /**
     * Is this a RECURRING, or subscription charge, IPN? This IPN is fired when a subscription
     * is successfully charged for the second time, and all times thereafter. For the first charge,
     * see isCharge.
     *
     * @return bool
     */
    public function isSubscriptionCharge()
    {
        return $this->getIPNType() === 'RECURRING';
    }

    /**
     * Is this a REFUND IPN? Refund IPNs are fired when a transaction is refunded
     *
     * @return bool
     */
    public function isRefund()
    {
        return $this->getIPNType() === 'REFUND';
    }

    /**
     * Is this a CC_CHARGE_FAILED, or charge failure, IPN? This IPN is fired when a credit
     * card payment fails. For subscriptions, also see isSubscriptionChargeFailure.
     *
     * @return bool
     */
    public function isChargeFailure()
    {
        return $this->getIPNType() === 'CC_CHARGE_FAILED';
    }

    /**
     * Is this a SUBSCRIPTION_CHARGE_FAILURE IPN? This IPN is fired when a credit
     * card charge fails for the second or subsequent charges for a subscription.
     * For the first charge, see isChargeFailure.
     *
     * @return bool
     */
    public function isSubscriptionChargeFailure()
    {
        return $this->getIPNType() === 'SUBSCRIPTION_CHARGE_FAILURE';
    }

    /**
     * Get the type of the IPN
     *
     * @return string|null
     */
    protected function getIPNType()
    {
        return $this->getParameter('transactionType');
    }

    /**
     * Returns the gateway's identifier for the transaction
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return $this->getParameter('referenceNumber') ?: null;
    }

    /**
     * Returns the gateway's identifier for the subscription
     *
     * @return string|null
     */
    public function getSubscriptionReference()
    {
        return $this->getParameter('subscriptionId') ?: null;
    }

    /**
     * Returns the gateway's identifier for the plan (Bluesnap contract)
     *
     * @return string|null
     */
    public function getPlanReference()
    {
        return $this->getParameter('contractId') ?: null;
    }

    /**
     * Returns the gateway's identifier for the customer
     *
     * @return string|null
     */
    public function getCustomerReference()
    {
        return $this->getParameter('accountId') ?: null;
    }

    /**
     * Returns the transaction amount
     *
     * @return string|null
     */
    public function getAmount()
    {
        $amount = $this->getParameter('invoiceAmount');
        return $amount === '' ? null : $amount;
    }

    /**
     * Returns the transaction currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->getParameter('currency') ?: null;
    }

    /**
     * Returns the time and date of the IPN callback
     *
     * @return DateTime|null
     */
    public function getDate()
    {
        $date = $this->getParameter('transactionDate');
        return $date ? new DateTime($date, new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)) : null;
    }

    /**
     * Get the value of a parameter returned in the IPN callback
     *
     * @param string $parameter
     * @return string|null
     */
    public function getParameter($parameter)
    {
        return isset($this->queryParams[$parameter]) ? $this->queryParams[$parameter] : null;
    }
}
