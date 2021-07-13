<?php


namespace Omnipay\BlueSnap\Message;


use Closure;
use DateTime;
use DateTimeZone;
use Exception;
use Omnipay\BlueSnap\Chargeback;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\CreditCard;
use Omnipay\BlueSnap\Subscription;
use Omnipay\BlueSnap\SubscriptionCharge;
use Omnipay\BlueSnap\Transaction;
use Omnipay\Common\Message\RequestInterface;
use SimpleXMLElement;

/**
 * BlueSnap response object for the Extended Payments API. See Request files or developer docs for usage.
 *
 * @link https://developers.bluesnap.com/v8976-Extended/docs
 *
 * @package Omnipay\BlueSnap\Message
 */
class ExtendedResponse extends AbstractResponse
{
     /**
     * @var Transaction|null
     */
    protected $transaction;

    /**
     * @var array<Refund>|null
     */
    protected $refunds;

    /**
     * @var array<Chargeback>|null
     */
    protected $chargebacks;

    /**
     * @var null|SimpleXMLElement
     */
    protected $transaction_invoice;

    /**
     * @var SimpleXMLElement[]
     */
    protected $chargeback_invoices;

    /**
     * @var SimpleXMLElement[]
     */
    protected $refund_invoices;

    /**
     * @param RequestInterface $request the initiating request.
     * @param mixed $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->transaction = null;
    }

    /**
     * Get the gateway's identifier for the customer
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getCustomerReference()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }
        if (isset($this->data->{'shopper-id'})) {
            return (string) $this->data->{'shopper-id'};
        }
        if (isset($this->data->{'shopper-info'}->{'shopper-id'})) {
            return (string) $this->data->{'shopper-info'}->{'shopper-id'};
        }
        if (isset($this->data->{'ordering-shopper'}->{'shopper-id'})) {
            return (string) $this->data->{'ordering-shopper'}->{'shopper-id'};
        }
        return null;
    }

     /**
     * Get the gateway's identifier for the transaction
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getTransactionReference()
    {
        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
            return (string) $invoice->{'invoice-id'};
        }
        if ($this->data instanceof SimpleXMLElement
            && isset($this->data->{'charge-invoice-info'}->{'invoice-id'})) {
            return (string) $this->data->{'charge-invoice-info'}->{'invoice-id'};
        }

        return null;
    }

    /**
     * Get the gateway's identifier for the subscription
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getSubscriptionReference()
    {
        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'subscription-id'})) {
            return (string) $this->data->{'subscription-id'};
        }
        return null;
    }

    /**
     * Get the monetary amount of a transaction or subscription
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getAmount()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }

        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
            return (string) $invoice->{'financial-transactions'}->{'financial-transaction'}->amount;
        }

        // if an override charge is set, that's the current amount, not the catalog (original) one
        if (isset($this->data->{'override-recurring-charge'}->amount)) {
            return (string) $this->data->{'override-recurring-charge'}->amount;
        }
        if (isset($this->data->{'catalog-recurring-charge'}->amount)) {
            return (string) $this->data->{'catalog-recurring-charge'}->amount;
        }
        if (isset($this->data->{'charge-invoice-info'}->{'invoice-amount'})) {
            return (string) $this->data->{'charge-invoice-info'}->{'invoice-amount'};
        }
        return null;
    }

    /**
     * Get the tax amount of a transaction or subscription
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getTax()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }
        // if an override charge is set, that's the current amount, not the catalog (original) one
        if (isset($this->data->{'cart'}->tax)) {
            return (string) $this->data->{'cart'}->tax;
        }
        return null;
    }

    /**
     * Get the currency of a transaction or subscription
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getCurrency()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }

        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
            return (string) $invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->currency;
        }

        // if an override charge is set, that's the current currency, not the catalog (original) one
        if (isset($this->data->{'override-recurring-charge'}->currency)) {
            return (string) $this->data->{'override-recurring-charge'}->currency;
        }
        if (isset($this->data->{'catalog-recurring-charge'}->currency)) {
            return (string) $this->data->{'catalog-recurring-charge'}->currency;
        }
        if (isset($this->data->{'charge-invoice-info'}->{'invoice-currency'})) {
            return (string) $this->data->{'charge-invoice-info'}->{'invoice-currency'};
        }
        return null;
    }

    /**
     * Get an array of subscription charges from a subscription.
     * will return the references of all charges made on that subscription.
     * The empty array will be returned if there are no charges.
     *
     * @return array<SubscriptionCharge>|null
     * @throws Exception
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedAssignment
     */
    public function getSubscriptionCharges()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }

        $subscriptionCharges = array();
        if (isset($this->data->{'subscription-charges'})) {
            foreach ($this->data->{'subscription-charges'}->children() as $charge) {
                if (isset($charge->{'charge-invoice-info'})) {
                    $params = array(
                        'date' => new DateTime(
                            (string) $charge->{'charge-invoice-info'}->{'date-created'},
                            new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)
                        ),
                        'transactionReference' => (string) $charge->{'charge-invoice-info'}->{'invoice-id'},
                        'amount' => (string) $charge->{'charge-invoice-info'}->{'invoice-amount'},
                        'currency' => (string) $charge->{'charge-invoice-info'}->{'invoice-currency'},
                        'customerReference' => (string) $this->getCustomerReference(),
                        'subscriptionReference' => (string) $this->getSubscriptionReference()
                    );
                    $subscriptionCharges[] = new SubscriptionCharge($params);
                }
            }
        }
        return !empty($subscriptionCharges) ? $subscriptionCharges : null;
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
        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'subscriptions'})) {
            foreach ($this->data->{'subscriptions'}->children() as $sub) {
                $subscriptions[] = new Subscription(array(
                    'subscriptionReference' => (string) $sub->{'subscription-id'},
                    'currency' => (string) $sub->{'catalog-recurring-charge'}->currency,
                    'amount' => (string) $sub->{'catalog-recurring-charge'}->amount,
                    'status' => (string) $sub->{'status'}
                ));
            }
            return $subscriptions;
        }
    }

    /**
     * Function to get a subscription status.
     *
     * Note that chargeback and refund statuses are not store here. To see if a transaction contains a refund or a
     * chargeback refer to the following:
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getStatus()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }
        // Check if we have invoice and invoice status
        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
            return (string)$invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->{'status'};
        }

        // check if status element is set and return
        if (isset($this->data->{'status'})) {
            return (string) $this->data->{'status'};
        }
        return null;
    }

    /**
     * Function to get a plan reference.
     *
     * @return string|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getPlanReference()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }
        // Check if we have invoice and invoice plan reference (Bluesnap contract)
        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
            return (string) $invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->{'skus'}
                ->{'sku'}
                ->{'sku-id'};
        }
        // check if underlying-sku-id element is set and return
        if (isset($this->data->{'underlying-sku-id'})) {
            return (string) $this->data->{'underlying-sku-id'};
        }
        return null;
    }

    /**
     * Get date the transaction or charge was created.
     * NOTE: BlueSnap only returns dates, not times.
     * The DateTime returned will be in the Etc/GMT+8 time zone.
     *
     * @return DateTime|null
     * @throws Exception
     * @psalm-suppress MixedPropertyFetch
     */
    public function getDateCreated()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }

        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement
            && isset($invoice->{'invoice-id'})) {
            return new DateTime(
                $invoice->{'financial-transactions'}
                    ->{'financial-transaction'}
                    ->{'date-created'},
                new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)
            );
        }

        $subscriptionCharges = $this->getSubscriptionCharges();
        if (!empty($subscriptionCharges)) {
            return $subscriptionCharges[0]->getDate();
        }

        if (isset($this->data->{'charge-invoice-info'}->{'date-created'})) {
            return new DateTime(
                $this->data->{'charge-invoice-info'}->{'date-created'},
                new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)
            );
        }
        return null;
    }

    /**
     * Get the date of the next charge for a subscription
     * NOTE: BlueSnap only returns dates, not times.
     * The DateTime returned will be in the Etc/GMT+8 time zone.
     *
     * @return DateTime|null
     * @throws Exception
     * @psalm-suppress MixedPropertyFetch
     */
    public function getNextChargeDate()
    {
        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'next-charge-date'})) {
            return new DateTime(
                $this->data->{'next-charge-date'},
                new DateTimeZone(Constants::BLUESNAP_TIME_ZONE)
            );
        }
        return null;
    }

    /**
     * Gets the credit card from a fetch transaction or subscription request
     *
     * @return null|CreditCard
     */
    public function getCard()
    {
        $cardParams = array();

        $cardXml = null;

        $invoice = $this->getTransactionInvoice();
        if ($invoice instanceof SimpleXMLElement
            && isset($invoice->{'financial-transactions'}->{'financial-transaction'}->{'credit-card'})
        ) {
            /**
             * @var SimpleXMLElement
             */
            $cardXml = $invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->{'credit-card'};
        } elseif ($this->data instanceof SimpleXMLElement && isset($this->data->{'credit-card'})) {
            /**
             * @var SimpleXMLElement
             */
            $cardXml = $this->data->{'credit-card'};
        }

        if ($cardXml) {
            if (isset($cardXml->{'card-type'})) {
                $cardParams['brand'] = strtolower((string) $cardXml->{'card-type'});
            }
            if (isset($cardXml->{'card-last-four-digits'})) {
                $cardParams['number'] = (string) $cardXml->{'card-last-four-digits'};
            }
            if (isset($cardXml->{'expiration-month'})) {
                $cardParams['expiryMonth'] = (string) $cardXml->{'expiration-month'};
            }
            if (isset($cardXml->{'expiration-year'})) {
                $cardParams['expiryYear'] = (string) $cardXml->{'expiration-year'};
            }
        }

        if ($invoice instanceof SimpleXMLElement && isset($invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->{'invoice-contact-info'})
        ) {
            /**
             * @var SimpleXMLElement
             */
            $contactXml = $invoice->{'financial-transactions'}
                ->{'financial-transaction'}
                ->{'invoice-contact-info'};
            if (isset($contactXml->{'first-name'})) {
                $cardParams['firstName'] = (string) $contactXml->{'first-name'};
            }
            if (isset($contactXml->{'last-name'})) {
                $cardParams['lastName'] = (string) $contactXml->{'last-name'};
            }
            if (isset($contactXml->email)) {
                $cardParams['email'] = (string) $contactXml->email;
            }
            if (isset($contactXml->state)) {
                $cardParams['state'] = (string) $contactXml->state;
            }
            if (isset($contactXml->country)) {
                $cardParams['country'] = (string) $contactXml->country;
            }
            if (isset($contactXml->zip)) {
                $cardParams['postcode'] = (string) $contactXml->zip;
            }
        }

        if (!empty($cardParams)) {
            return new CreditCard($cardParams);
        }
        return null;
    }

    /**
     * @return Transaction|null
     * @throws Exception
     */
    public function getTransaction()
    {
        if (!isset($this->transaction)) {
            $invoice = $this->getTransactionInvoice();
            if ($invoice !== null) {
                $params = array(
                    'amount' => $this->getAmount(),
                    'currency' => $this->getCurrency(),
                    'customerReference' => $this->getCustomerReference(),
                    'date' => $this->getDateCreated(),
                    'status' => $this->getStatus(), // Note chargebacks
                    'transactionReference' => $this->getTransactionReference(),
                );
                $this->transaction = new Transaction($params);
            }
        }

        return $this->transaction;
    }

    /**
     * Returns refunds for the corresponding transaction that was fetched
     *
     * @return Refund[]|null
     */
    public function getRefunds()
    {
        if (empty($this->refunds)) {
            $invoices = $this->getRefundInvoices();
            foreach ($invoices as $invoice) {
                if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
                    /** @var SimpleXMLElement */
                    $financial_transaction = $invoice->{'financial-transactions'}->{'financial-transaction'};
                    $params = array(
                        'amount' => (string) $financial_transaction->amount,
                        'currency' => (string) $financial_transaction->currency,
                        'refundReference' => (string) $invoice->{'invoice-id'},
                        'refundId' => (string) $invoice->{'invoice-id'},
                        'time' => (string) $financial_transaction->{'date-created'},
                        'transactionReference' => (string) $invoice->{'original-invoice-id'},
                    );
                    $this->refunds[] = new Refund($params);
                }
            }
        }

        return $this->refunds;
    }

    /**
     * Returns chargeback for the corresponding transaction that was fetched
     *
     * @return Chargeback[]|null
     */
    public function getChargebacks()
    {
        if (empty($this->chargebacks)) {
            $invoices = $this->getChargebackInvoices();
            foreach ($invoices as $invoice) {
                if ($invoice instanceof SimpleXMLElement && isset($invoice->{'invoice-id'})) {
                    /** @var SimpleXMLElement */
                    $financial_transaction = $invoice->{'financial-transactions'}->{'financial-transaction'};
                    $params = array(
                        'amount' => (string) $financial_transaction->amount,
                        'currency' => (string) $financial_transaction->currency,
                        'chargebackReference' => (string) $invoice->{'invoice-id'},
                        'processorReceivedTime' => (string) $financial_transaction->{'date-created'},
                        'status' => Constants::REVERSAL_CHARGEBACK,
                        'transactionReference' => (string) $invoice->{'original-invoice-id'},
                    );
                    $this->chargebacks[] = new Chargeback($params);
                }
            }
        }

        return $this->chargebacks;
    }

    /**
     * Get the value of a custom parameter by name
     *
     * @param string $name
     * @return string|null
     */
    public function getCustomParameter($name)
    {
        if (!$this->data instanceof SimpleXMLElement
            || !$this->data->cart instanceof SimpleXMLElement
            || !isset($this->data->cart->{'cart-item'})) {
            return null;
        }

        /**
         * @var SimpleXMLElement
         */
        $parameters = $this->data->cart->{'cart-item'}->{'sku-parameter'};
        /**
         * @var SimpleXMLElement
         */
        foreach ($parameters as $parameter) {
            if ($name === (string) $parameter->{'param-name'}) {
                return (string) $parameter->{'param-value'};
            }
        }
        return null;
    }

    /**
     * Returns the invoice element corresponding to the requested transaction.
     *
     * @return SimpleXMLElement|null
     */
    protected function getTransactionInvoice()
    {
        if ($this->transaction_invoice !== null) {
            return $this->transaction_invoice;
        }

        $extract_invoice_func = $this->getInvoiceFilterForEventType();

        /** @var SimpleXMLElement|null */
        $this->transaction_invoice = $extract_invoice_func();
        return $this->transaction_invoice;
    }

    /**
     * Returns all refund invoice elements that match the request's transaction reference.
     *
     * @return SimpleXMLElement[]
     */
    public function getRefundInvoices()
    {
        if ($this->refund_invoices !== null) {
            return $this->refund_invoices;
        }

        $extract_invoice_func = $this->getInvoiceFilterForEventType(Constants::REVERSAL_REFUND);

        /** @var SimpleXMLElement[] */
        $this->refund_invoices = $extract_invoice_func();
        return $this->refund_invoices;
    }

    /**
     * Returns all chargeback invoice elements that match the request's transaction reference.
     *
     * @return SimpleXMLElement[]
     */
    public function getChargebackInvoices()
    {
        if ($this->chargeback_invoices !== null) {
            return $this->chargeback_invoices;
        }

        $extract_invoice_func = $this->getInvoiceFilterForEventType(Constants::REVERSAL_CHARGEBACK);

        /** @var SimpleXMLElement[] */
        $this->chargeback_invoices = $extract_invoice_func();
        return $this->chargeback_invoices;
    }

    /**
     * Returns all of the invoices contained in the response data.
     *
     * When you fetch an order, it may contain multiple invoices (for example, one for each subscription charge).
     *
     * @return SimpleXMLElement|null
     */
    protected function getInvoices()
    {
        $invoices = null;

        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'post-sale-info'}->invoices)) {
            /** @var SimpleXMLElement $invoices */
            $invoices = $this->data->{'post-sale-info'}->invoices;
        }

        return $invoices;
    }

    /**
     * Returns a closure that is specific to an event type that retrieves all invoices associated with that event.
     *
     * When retrieving a single charge, the closure will return on the first transaction reference match it finds.
     *
     * A response can also contain incidents (refunds, chargebacks) in the response. BlueSnap refers to these as
     * reversal types. This is currently the only known way of retrieving refunds/chargebacks from an order response
     * in the Extended API. Refunds can contain multiple invoices if the transaction was refunded in parts. Because of
     * this, we need to extract all invoices containing reversals. Although currently unable to verify if chargebacks
     * follow the same behavior, there is currently no min or max bounds for the in documents for either reversal.
     * Because of this, we assume that a response can also contain multiple chargeback invoices.
     *
     * @param string|null $reversal_type
     *
     * @return Closure
     */
    private function getInvoiceFilterForEventType($reversal_type = null)
    {
        $request = $this->getRequest();
        $transaction_reference = $request->getTransactionReference();
        $invoices = $this->getInvoices();

        $fn = null;
        if ($reversal_type !== null) {
            $fn =
                /**
                 * @return SimpleXMLElement[]
                 */
                function () use ($invoices, $reversal_type, $transaction_reference) {
                    if ($invoices === null) {
                        return array();
                    }

                    $reversal_invoices = array();
                    /** @var SimpleXMLElement */
                    foreach ($invoices->children() as $invoice) {
                        if (isset($invoice->{'reversal-type'}, $invoice->{'original-invoice-id'})
                            && (string)$invoice->{'reversal-type'} === $reversal_type
                            && (string)$invoice->{'original-invoice-id'} === $transaction_reference) {
                            $reversal_invoices[] = $invoice;
                        }
                    }
                    return $reversal_invoices;
                };
        } else {
            $fn =
                /**
                 * @return SimpleXMLElement|null
                 */
                function () use ($invoices, $transaction_reference) {
                    if ($invoices === null) {
                        return null;
                    }

                    $invoice_match = null;
                    /** @var SimpleXMLElement */
                    foreach ($invoices->children() as $invoice) {
                        if (isset($invoice->{'invoice-id'})
                            && (string) $invoice->{'invoice-id'} === $transaction_reference) {
                            $invoice_match = $invoice;
                            break;
                        }
                    }

                    return $invoice_match;
                };
        }

        return $fn;
    }
}
