<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Constants;

/**
 * Fetch a transaction.
 *
 * The extended payments API refers to transactions as "invoices". You can't fetch them
 * directly, but they're contained in "Orders" and referenced in "Subscription Charges".
 * This request makes it seem as though you can fetch them directly.
 *
 * Parameters:
 * - transactionReference (required): The gateway's identifier for the transaction
 * to be fetched.
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
 *   // captured, you'll receive an IPN callback, at which point you can fetch the transaction:
 *
 *   // not a request, so no ->send() call
 *   $ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
 *   if ($ipnCallback->isCharge()) {
 *       $transactionResponse = $gateway->fetchTransaction(array(
 *           'transactionReference' => $ipnCallback->getTransactionReference()
 *       ))->send();
 *
 *       if ($transactionResponse->isSuccessful()) {
 *           echo 'Currency: ' . $transactionResponse->getCurrency() . PHP_EOL;
 *           echo 'Amount: ' . $transactionResponse->getAmount() . PHP_EOL;
 *           echo 'Date created: ' . $transactionResponse->getDateCreated()->format('Y-m-d') . PHP_EOL;
 *           echo 'Customer Reference: ' . $transactionResponse->getCustomerReference() . PHP_EOL;
 *           echo 'Custom parameter "foo": ' . $transactionResponse->getCustomParameter('foo') . PHP_EOL;
 *           var_dump($transactionResponse->getCard());
 *       } else {
 *           // error handling
 *       }
 *   }
 * </code>
 */
class ExtendedFetchTransactionRequest extends ExtendedAbstractRequest
{
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
        return parent::getEndpoint() . '/orders/resolve?invoiceId='
                                     . strval($this->getTransactionReference());
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
