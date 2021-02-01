<?php

namespace Omnipay\BlueSnap\Message;

use DateTime;
use DateTimeZone;
use Exception;
use Omnipay\BlueSnap\Chargeback;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\Subscription;
use Omnipay\BlueSnap\Transaction;

/**
 * BlueSnap response object for the Reporting API. See Reporting*Request files or developer docs for usage.
 *
 * @link https://developers.bluesnap.com/v8976-Tools/docs/reporting-api-overview
 */
class ReportingResponse extends AbstractResponse
{
    /**
     * @var Refund[]|null
     */
    protected $refunds;

    /**
     * @var Chargeback[]|null
     */
    protected $chargebacks;

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

        /** @var array<string, string> */
        foreach ($this->data['data'] as $row) {
            $params = array(
                'amount' => $row['Merchant Sales (Auth Currency)'],
                'currency' => $row['Auth. Currency'],
                'customerReference' => $row['Shopper ID'],
                'date' => new DateTime($row['Transaction Date'], new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)),
                'status' => $row['Transaction Type'], /** @see Types::TRANSACTION_* for possible values */
                'transactionReference' => $row['Invoice ID'],
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

    /**
     * @return Refund[]|null
     * @throws Exception
     */
    public function getRefunds()
    {
        if (!empty($this->refunds)) {
            return $this->refunds;
        }

        if (!is_array($this->data) || !isset($this->data['data'])) {
            return null;
        }

         /** @var array<string, string> $row */
        foreach ($this->data['data'] as $row) {
            /**
             * If ReportingFetchTransactionsRequest::setTransactionType(Constants::TRANSACTION_TYPE_REFUND) was used
             * then these should always be of type 'Refund'. This check is added as extra layer of safety to
             * ensure only transactions with chargebacks are included. If no transaction type is used in the request,
             * all transaction types (sales, refunds and chargebacks) will returned.
             */
            if ($row['Transaction Type'] !== Constants::TRANSACTION_TYPE_REFUND) {
                continue;
            }
            $params = array(
                'amount' => $row['Merchant Sales (Auth Currency)'],
                'currency' => $row['Auth. Currency'],
                'customerReference' => $row['Shopper ID'],
                'time' => new DateTime($row['Transaction Date'], new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)),
                'reason' => $row['Refund / Chargeback Reason'],
                'refundReference' => $row['Invoice ID'],
                'transactionReference' => $row['Original Invoice ID'],
            );

            $this->refunds[] = new Refund($params);
        }
        return $this->refunds;
    }

    /**
     * @return Chargeback[]|null
     * @throws Exception
     */
    public function getChargebacks()
    {
        if (!empty($this->chargebacks)) {
            return $this->chargebacks;
        }

        if (!is_array($this->data) || !isset($this->data['data'])) {
            return null;
        }

         /** @var array<string, string> $row */
        foreach ($this->data['data'] as $row) {
            /**
             * If ReportingFetchTransactionsRequest::setTransactionType(Constants::TRANSACTION_TYPE_CHARGEBACK) was
             * used then these should always be of type 'Chargeback'. This check is added as extra layer of safety to
             * ensure only refunded transactions are included. If no transaction type is used in the request all
             * transaction types (sales, refunds and chargebacks) will returned,
             */
            if ($row['Transaction Type'] !== Constants::TRANSACTION_TYPE_CHARGEBACK) {
                continue;
            }

            $params = array(
                'amount' => $row['Merchant Sales (Auth Currency)'],
                'currency' => $row['Auth. Currency'],
                'customerReference' => $row['Shopper ID'],
                'processorReceivedTime' => new DateTime(
                    $row['Transaction Date'],
                    new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)
                ),
                'reason' => $row['Refund / Chargeback Reason'],
                'chargebackReference' => $row['Invoice ID'],
                'transactionReference' => $row['Original Invoice ID'],
            );
            $this->chargebacks[] = new Chargeback($params);
        }
        return $this->chargebacks;
    }
}
