<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Refund a transaction.
 *
 * Parameters:
 * - transactionReference (required): The gateway's identifier for the transaction
 * to be refunded.
 * - amount (optional): The amount to refund (leave this out to refund the total amount)
 * - currency (optional): The currency of the transaction. BlueSnap does not need this
 * and will ignore it, but Omnipay enforces valid amounts for the specified currency,
 * so it should be provided if you specify an amount.
 * - reason (optional): The reason for refunding.
 * - cancelSubscriptions (optional, default false): Boolean representing whether the
 * associated subscriptions should be canceled.
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
 *   // captured, you'll receive an IPN callback, at which point you can refund the transaction:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $refundResponse = $gateway->refund(array(
 *           'transactionReference' => $ipnCallback->getTransactionReference()
 *       ))->send();
 *
 *       if ($refundResponse->isSuccessful()) {
 *           // do stuff
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedRefundRequest extends ExtendedAbstractRequest
{
    /**
     * Gets the refund reason
     *
     * @return string|null
     */
    public function getReason()
    {
        return strval($this->getParameter('reason')) ?: null;
    }

    /**
     * Sets the refund reason
     *
     * @param string $value
     * @return static
     */
    public function setReason($value)
    {
        return $this->setParameter('reason', $value);
    }

    /**
     * Gets whether subscriptions should be canceled
     *
     * @return bool|null
     */
    public function getCancelSubscriptions()
    {
        /**
         * @var bool|null
         */
        return $this->getParameter('cancelSubscriptions');
    }

    /**
     * Sets whether subscriptions should be canceled (default false)
     *
     * @param bool $value
     * @return static
     */
    public function setCancelSubscriptions($value)
    {
        return $this->setParameter('cancelSubscriptions', $value);
    }

    /**
     * @return null
     */
    public function getData()
    {
        $this->validate('transactionReference');
        return null;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        $params = array(
            'invoiceId' => $this->getTransactionReference()
        );
        $amount = $this->getAmount();
        if (isset($amount)) {
            $params['amount'] = $amount;
        }
        $reason = $this->getReason();
        if (isset($reason)) {
            $params['reason'] = $reason;
        }
        // false is default
        $params['cancelSubscriptions'] = $this->getCancelSubscriptions() ? 'true' : 'false';

        return parent::getEndpoint() . '/orders/refund?' . http_build_query($params);
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
