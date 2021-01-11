<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Fetch all transactions in a time range.
 *
 * If you only want to fetch a single transaction, see ExtendedFetchTransactionRequest.
 *
 * Parameters:
 * - startTime (required): The beginning of the time range. The date must be in the 'Etc/GMT+8'
 * time zone. 'Etc/GMT+8' meants GMT-8, also known as PST. BlueSnap does not observe daylight
 * savings. BlueSnap also does not accept hours, minutes, or seconds. Dates only.
 * - endTime (required); The end of the time range. The same rules as above apply.
 *
 * <code>
 *   // Set up the gateway
 *   $gateway = \Omnipay\Omnipay::create('BlueSnap'); // BlueSnap_Extended and
 *                                                    // BlueSnap_HostedCheckout work too
 *   $gateway->setUsername('your_username');
 *   $gateway->setPassword('y0ur_p4ssw0rd');
 *   $gateway->setTestMode(false);
 *
 *   // Lots of users make lots of purchases...
 *
 *   // Now we fetch those transactions:
 *   $response = $gateway->fetchTransactions(array(
 *       'startTime' => new DateTime('2016-09-01', new DateTimeZone('Etc/GMT+8')),
 *       'startTime' => new DateTime('2016-09-02', new DateTimeZone('Etc/GMT+8'))
 *   ))->send();
 *
 *   if ($response->isSuccessful()) {
 *       foreach ($response->getTransactions() as $transaction) {
 *           echo 'Transaction reference: ' . $transaction->getTransactionReference() . PHP_EOL;
 *           echo 'Customer reference: ' . $customer->getCustomerReference() . PHP_EOL;
 *           echo 'Currency: ' . $transaction->getCurrency() . PHP_EOL;
 *           echo 'Amount: ' . $transaction->getAmount() . PHP_EOL;
 *           echo 'Date: ' . $transaction->getDate()->format('Y-m-d') . PHP_EOL;
 *           echo 'Custom parameter 1: ' . $transaction->getCustomParameter1() . PHP_EOL;
 *           echo 'Custom parameter 8: ' . $transaction->getCustomParameter8() . PHP_EOL;
 *       }
 *   } else {
 *       // error handling
 *   }
 * </code>
 */
class FetchTransactionsRequest extends ReportingAbstractRequest
{
    const REPORT_NAME = 'TransactionDetail';
}
