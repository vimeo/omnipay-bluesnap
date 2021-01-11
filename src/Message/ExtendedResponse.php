<?php


namespace Omnipay\BlueSnap\Message;


use Closure;
use DateTime;
use DateTimeZone;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\CreditCard;
use Omnipay\BlueSnap\Subscription;
use Omnipay\BlueSnap\SubscriptionCharge;
use Omnipay\BlueSnap\Transaction;
use Omnipay\BlueSnap\Types;
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
     * @var SimpleXMLElement|null
     */
    private $invoice;

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
        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return (string) $this->invoice->{'invoice-id'};
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

        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return (string) $this->invoice->{'financial-transactions'}->{'financial-transaction'}->amount;
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
        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return (string) $this->invoice->{'financial-transactions'}->{'financial-transaction'}->currency;
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
     * @see Response::getChargebacks() or
     * @see Response::getRefunds()
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
        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return (string)$this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'status'};
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
        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return (string) $this->invoice->{'financial-transactions'}
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
     * @psalm-suppress MixedPropertyFetch
     */
    public function getDateCreated()
    {
        if (!$this->data instanceof SimpleXMLElement) {
            return null;
        }

        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement && isset($this->invoice->{'invoice-id'})) {
            return new DateTime(
                $this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'date-created'},
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
     * Get the decrypted parameters from the return url after a HostedCheckoutDecryptReturnUrlRequest
     * Returns an array of paramName => paramValue
     *
     * @return array|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getDecryptedParameters()
    {
        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'decrypted-token'})) {
            parse_str((string) $this->data->{'decrypted-token'}, $result);
            return $result ?: null;
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
        $this->invoice = $this->getInvoice();
        if ($this->invoice instanceof SimpleXMLElement
            && isset($this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'credit-card'})
        ) {
            /**
             * @var SimpleXMLElement
             */
            $cardXml = $this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'credit-card'};
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

        if ($this->invoice instanceof SimpleXMLElement
            && isset($this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'invoice-contact-info'})
        ) {
            /**
             * @var SimpleXMLElement
             */
            $contactXml = $this->invoice->{'financial-transactions'}->{'financial-transaction'}->{'invoice-contact-info'};
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
     */
    public function getTransaction()
    {
        if (!isset($this->transaction)) {
            $this->invoice = $this->getInvoice();
            if ($this->invoice !== null) {
                $params = [
                    'amount' => $this->getAmount(),
                    'currency' => $this->getCurrency(),
                    'customerReference' => $this->getCustomerReference(),
                    'date' => $this->getDateCreated(),
                    'status' => $this->getStatus(), // Note chargebacks
                    'transactionReference' => $this->getTransactionReference(),
                ];
                $this->transaction = new Transaction($params);
            }
        }

        return $this->transaction;
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
     * Returns the invoice element corresponding to the requested transaction
     *
     * When you fetch an order, it may contain multiple invoices (for example,
     * one for each subscription charge), so this function selects the correct
     * one.
     *
     * @return SimpleXMLElement|null
     */
    protected function getInvoice()
    {
        if ($this->invoice) {
            return $this->invoice;
        }

        $this->invoice = null;
        // find the invoice that was reqeusted
        $request = $this->getRequest();
        $transactionReference = $request->getTransactionReference();

        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'post-sale-info'}->invoices)) {
            /**
             * @var SimpleXMLElement
             */
            $invoices = $this->data->{'post-sale-info'}->invoices;
            /**
             * @var SimpleXMLElement
             */
            foreach ($invoices->children() as $invoice) {
                if (strval($invoice->{'invoice-id'}) === $transactionReference) {
                    $this->invoice = $invoice;
                    break;
                }
            }
        }
        return $this->invoice;
    }
}
