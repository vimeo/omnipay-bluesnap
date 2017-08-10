<?php

namespace Omnipay\BlueSnap\Test\Framework;

use DateTime;
use InvalidArgumentException;
use Omnipay\Common\Currency;
use Omnipay\BlueSnap\UrlParameter;
use Omnipay\BlueSnap\UrlParameterBag;
use Omnipay\BlueSnap\Constants;
use Omnipay\BlueSnap\CreditCard;

/**
 * Generates fake data for use in test cases. Perhaps one day Omnipay
 * could start using something like this or
 * https://github.com/fzaninotto/Faker
 */
class DataFaker
{
    const HEX_CHARACTERS = '0123456789abcdef';
    const ALPHABET_LOWER = 'abcdefghijklmnopqrstuvwxyz';
    const ALPHABET_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const DIGITS = '0123456789';

    public function __construct()
    {
        mt_srand();
        date_default_timezone_set(Constants::BLUESNAP_TIME_ZONE);
    }

    /**
     * Returns an int between $min and $max, inclusive
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    public function intBetween($min, $max)
    {
        return mt_rand($min, $max);
    }

    /**
     * Returns a bool
     *
     * @return bool
     */
    public function bool()
    {
        return mt_rand(0, 1) ? true : false;
    }

    /**
     * Returns a monetary amount that is valid in the provided currency
     *
     * @param string $currency
     * @return string
     */
    public function monetaryAmount($currency)
    {
        /**
         * @var Currency|null
         */
        $currency = Currency::find($currency);
        if ($currency === null) {
            throw new InvalidArgumentException('This currency is not supported.');
        }

        $currencyPrecision = $currency->getDecimals();

        $integerComponent = strval($this->intBetween($currencyPrecision > 0 ? 0 : 1, 999));

        if ($currencyPrecision === 0) {
            return $integerComponent;
        }

        $decimalComponent = $this->intBetween(
            $integerComponent > 0 ? 0 : 1,
            intval(str_repeat('9', $currencyPrecision))
        );
        $decimalComponent = str_pad(strval($decimalComponent), $currencyPrecision, '0', STR_PAD_LEFT);

        return $integerComponent . '.' . $decimalComponent;
    }

    /**
     * Return a three letter currency code
     *
     * @return string
     */
    public function currency()
    {
        $currencies = array_keys(Currency::all());
        /**
         * @var string
         */
        return $currencies[$this->intBetween(0, count($currencies) - 1)];
    }

    /**
     * Return a name (first or last)
     *
     * @return string
     */
    public function name()
    {
        return ucfirst($this->randomCharacters(self::ALPHABET_LOWER, $this->intBetween(3, 10)));
    }

    /**
     * Return an ip address
     *
     * @return string
     */
    public function ipAddress()
    {
        return implode('.', array(
            $this->intBetween(0, 255),
            $this->intBetween(0, 255),
            $this->intBetween(0, 255),
            $this->intBetween(0, 255)
        ));
    }

    /**
     * Return an email address
     *
     * @return string
     */
    public function email()
    {
        return $this->randomCharacters(self::DIGITS . self::ALPHABET_LOWER, $this->intBetween(3, 10))
                                                    . '@example.'
                                                    . $this->topLevelDomain();
    }

    /**
     * Return a url
     *
     * @return string
     */
    public function url()
    {
        return 'http://www.example.'
            . $this->topLevelDomain()
            . '/'
            . $this->randomCharacters(self::DIGITS . self::ALPHABET_LOWER . '%', $this->intBetween(0, 10));
    }

    /**
     * Returns a query string. If $numParams is not set, a random number
     * of parameters will be chosen.
     *
     * @param int $numParams
     * @return string
     */
    public function queryString($numParams = null)
    {
        $numParams = $numParams ?: $this->intBetween(1, 10);
        $params = array();
        for ($i = 0; $i < $numParams; $i++) {
            $paramName = $this->randomCharacters(self::ALPHABET_LOWER . self::ALPHABET_UPPER, $this->intBetween(4, 10));
            $paramValue = $this->randomCharacters(self::ALPHABET_LOWER . self::DIGITS, $this->intBetween(1, 10));
            $params[$paramName] = $paramValue;
        }
        return http_build_query($params);
    }

    /**
     * @return string
     */
    protected function topLevelDomain()
    {
        switch ($this->intBetween(0, 3)) {
            case 0:
                return 'com';

            case 1:
                return 'org';

            case 2:
                return 'net';

            default:
                return 'edu';
        }
    }

    /**
     * Return a two-letter region (eg country or state) code
     *
     * @return string
     */
    public function region()
    {
        return $this->randomCharacters(self::ALPHABET_UPPER, 2);
    }

    /**
     * Return a 5 digit postal code
     *
     * @return string
     */
    public function postcode()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, 5);
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a string of random characters from $characterSet that is
     * $numCharacters long
     *
     * @param string $characterSet
     * @param int $numCharacters
     * @return string
     */
    public function randomCharacters($characterSet, $numCharacters)
    {
        if ($numCharacters < 0) {
            throw new InvalidArgumentException(
                'Parameter numCharacters must be positive or zero, saw ' . strval($numCharacters)
            );
        }
        if (empty($characterSet)) {
            throw new InvalidArgumentException('characterSet must not be empty');
        }

        $result = '';
        $setLength = strlen($characterSet);
        for ($i = 0; $i < $numCharacters; $i++) {
            $result .= $characterSet[$this->intBetween(0, $setLength - 1)];
        }

        return $result;
    }

    /**
     * Return a timestamp in the format used by BlueSnap's API responses
     *
     * @return string
     */
    public function timestamp()
    {
        $now = time();
        return date('d-M-y', $this->intBetween($now - 100000000, $now + 100000000));
    }

    /**
     * Return a DateTime (no time will be set, only a date, because that's all BlueSnap supports)
     *
     * @return DateTime
     */
    public function datetime()
    {
        return new DateTime($this->timestamp());
    }

    /**
     * @return string
     */
    public function username()
    {
        return 'API_' . $this->randomCharacters(self::DIGITS, $this->intBetween(20, 24));
    }

    /**
     * @return string
     */
    public function password()
    {
        return $this->randomCharacters(self::ALPHABET_LOWER . self::DIGITS, $this->intBetween(8, 16));
    }

    /**
     * Return a customer reference
     *
     * @return string
     */
    public function customerReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(6, 10));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a transaction reference
     *
     * @return string
     */
    public function transactionReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(6, 10));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a subscription reference
     *
     * @return string
     */
    public function subscriptionReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(6, 10));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a subscription charge reference
     *
     * @return string
     */
    public function subscriptionChargeReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(3, 10));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a store reference
     *
     * @return string
     */
    public function storeReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(3, 7));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a plan reference
     *
     * @return string
     */
    public function planReference()
    {
        do {
            $result = $this->randomCharacters(self::DIGITS, $this->intBetween(6, 10));
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a UrlParameter
     *
     * @return UrlParameter
     */
    public function urlParameter()
    {
        return new UrlParameter($this->urlParameterAsArray());
    }

    /**
     * Return a UrlParameter as an array
     *
     * @return array
     */
    public function urlParameterAsArray()
    {
        return array(
            'key' => $this->randomCharacters(
                DataFaker::ALPHABET_LOWER,
                $this->intBetween(3, 15)
            ),
            'value' => $this->randomCharacters(
                DataFaker::ALPHABET_LOWER . DataFaker::DIGITS,
                $this->intBetween(1, 3)
            )
        );
    }

    /**
     * Return some UrlParameters
     *
     * @return UrlParameterBag
     */
    public function urlParameters()
    {
        $urlParameters = new UrlParameterBag();
        for ($i = 0; $i < $this->intBetween(1, 5); $i++) {
            $urlParameters->add($this->urlParameter());
        }
        return $urlParameters;
    }

    /**
     * Return some UrlParameters as an array
     *
     * @return array
     */
    public function urlParametersAsArray()
    {
        $urlParameters = array();
        for ($i = 0; $i < $this->intBetween(1, 5); $i++) {
            $urlParameters[] = $this->urlParameterAsArray();
        }
        return $urlParameters;
    }

    /**
     * Return encrypted URL parameters
     *
     * @return string
     */
    public function encryptedUrlParameters()
    {
        do {
            $result = $this->randomCharacters(
                self::DIGITS . self::ALPHABET_LOWER . self::ALPHABET_UPPER,
                $this->intBetween(40, 100)
            );
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a custom parameter for a transaction
     *
     * @return string
     */
    public function customParameter()
    {
        do {
            $result = $this->randomCharacters(
                self::DIGITS . self::ALPHABET_UPPER . self::ALPHABET_LOWER,
                $this->intBetween(1, 20)
            );
        } while ($result == 0);
        return $result;
    }

    /**
     * Return a credit card brand
     *
     * @return string
     */
    public function cardBrand()
    {
        $card = new \Omnipay\Common\CreditCard();
        /**
         * @var string
         */
        return array_rand($card->getSupportedBrands());
    }

    /**
     * Return a fake card
     *
     * @return CreditCard
     */
    public function card()
    {
        $now = new DateTime();
        $now2 = clone $now;
        return new CreditCard(array(
            'number' => $this->intBetween(0, 1) ? '4242424242424242' : '3530111333300000',
            'expiryMonth' => str_pad(strval($this->intBetween(1, 12)), 2, '0', STR_PAD_LEFT),
            'expiryYear' => strval($this->intBetween(
                intval($now->modify('+1 year')->format('Y')),
                intval($now2->modify('+50 year')->format('Y'))
            )),
            'brand' => $this->cardBrand(),
            'country' => $this->region(),
            'state' => $this->region(),
            'postcode' => $this->postcode(),
            'email' => $this->email(),
            'firstName' => $this->name(),
            'lastName' => $this->name()
        ));
    }

    /**
     * Return transaction status
     *
     * @return string
     */
    public function transactionStatus()
    {
        $statuses = array('Approved', 'Canceled', 'Declined', 'Pending', 'Error');
        $index = $this->intBetween(0, 4);
        return $statuses[$index];
    }
    /**
     * Return subscription status
     *
     * @return string
     */
    public function subscriptionStatus()
    {
        $statuses = array('A', 'C', 'D');
        $index = $this->intBetween(0, 2);
        return $statuses[$index];
    }
}
