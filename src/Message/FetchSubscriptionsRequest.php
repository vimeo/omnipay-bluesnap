<?php

namespace Omnipay\BlueSnap\Message;

/**
 * Fetch all subscriptions in a time range.
 *
 * If you want to fetch all canceled subscriptions, see FetchCanceledSubscriptionsRequest.
 * If you only want to fetch a single subscription, see ExtendedFetchSubscriptionRequest.
 * If you want to fetch all subscriptions by customer, see ExtendedFetchSubscriptionsRequest.
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
 *   // Now we fetch those subscriptions:
 *   $response = $gateway->fetchSubscriptions(array(
 *       'startTime' => new DateTime('2016-09-01', new DateTimeZone('Etc/GMT+8')),
 *       'startTime' => new DateTime('2016-09-02', new DateTimeZone('Etc/GMT+8'))
 *   ))->send();
 *
 *   if ($response->isSuccessful()) {
 *       foreach ($response->getSubscriptions() as $subscription) {
 *           echo 'Subscription reference: ' . $subscription->getSubscriptionReference() . PHP_EOL;
 *           echo 'Currency: ' . $subscription->getCurrency() . PHP_EOL;
 *           echo 'Amount: ' . $subscription->getAmount() . PHP_EOL;
 *       }
 *   } else {
 *       // error handling
 *   }
 * </code>
 */
class FetchSubscriptionsRequest extends FetchTransactionsRequest
{
    const REPORT_NAME = 'ActiveSubscriptions';
}
