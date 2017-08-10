<?php

namespace Omnipay\BlueSnap;

/**
 * Extension of Omnipay Credit Card object to provide additional functionality.
 */
class CreditCard extends \Omnipay\Common\CreditCard
{
    /**
     * Gets the card brand. Returns the brand that was set if one was set,
     * otherwise determines the brand from the card number.
     *
     * @return string|null
     */
    public function getBrand()
    {
        $brand = strval($this->getParameter('brand'));
        if ($brand) {
            return $brand;
        }

        /**
         * @var string|null
         */
        return parent::getBrand();
    }

    /**
     * Sets the card brand
     *
     * @param string $value
     * @return static
     */
    public function setBrand($value)
    {
        /**
         * @var static
         */
        return $this->setParameter('brand', $value);
    }
}
