<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use DateTimeZone;
use Exception;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\Subscription;
use Omnipay\BlueSnap\Transaction;
use SimpleXMLElement;

/**
 * BlueSnap response object for the (non-extended) Payments API. See Request files or developer docs for usage.
 *
 * @link https://developers.bluesnap.com/v8976-JSON/docs
 */
class Response extends AbstractResponse
{
    /**
     * Retrieves multiple transactions data from the response.
     *
     * This data can only be retrieved if the request was issued via @link ReportingFetchTransactionsRequest which
     * uses the Reporting API. If the transaction data was retrieved via @link ExtendedFetchTransactionRequest this
     * method will return null. To retrieve transaction data from the extended request see
     * @link ExtendedResponse::getTransaction()
     *
     * @return array<Transaction>|null
     * @throws Exception
     */
    public function getTransactions()
    {
        if (!is_array($this->data) || !isset($this->data['data'])) {
            return null;
        }
        $transactions = array();
        /**
         * @var array<string, string>
         */
        foreach ($this->data['data'] as $row) {
            $params = array(
                'transactionReference' => $row['Invoice ID'],
                'date' => new DateTime($row['Transaction Date'], new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)),
                'currency' => $row['Auth. Currency'],
                'amount' => $row['Merchant Sales (Auth Currency)'],
                'customerReference' => $row['Shopper ID']
            );
            // grab all the custom parameters
            foreach ($row as $field => $value) {
                if (strpos($field, 'Custom Field') !== false && $value !== '') {
                    $parts = explode(' ', $field);
                    $index = $parts[2];
                    $params['customParameter' . $index] = $value;
                }
            }
            $transactions[] = new Transaction($params);
        }
        return $transactions;
    }

    /**
     * @return array<Subscription>|null
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedAssignment
     */
    public function getSubscriptions()
    {
        // data can be SimpleXMLElement or JSON object
        if (!isset($this->data) && !isset($this->data['data'])) {
            return null;
        }

        $subscriptions = array();
        if (is_array($this->data)) {
            /** @var array<string, string> */
            foreach ($this->data['data'] as $row) {
                $subscriptions[] = new Subscription(array(
                    'subscriptionReference' => $row['Subscription ID'],
                    'currency' => $row['Auth. Currency'],
                    'amount' => isset($row['Price (Auth. Currency)'])
                    ? $row['Price (Auth. Currency)']
                    : $row['Last Charge Price (Auth. Currency)']
                ));
            }
        }
        return $subscriptions;
    }
}
