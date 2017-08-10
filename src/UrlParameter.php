<?php

namespace Omnipay\BlueSnap;

use Omnipay\Common\Helper;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * UrlParameter
 *
 * Definites a parameter that can appear in a URL's query string. A parameter
 * has a key and a value.
 */
class UrlParameter
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new UrlParameter with the specified parameters
     *
     * @param array<mixed> $parameters
     */
    public function __construct($parameters = array())
    {
        $this->initialize($parameters);
    }

    /**
     * Initialize this UrlParameter with the specified parameters
     *
     * @param array<mixed> $parameters
     * @return static
     */
    public function initialize($parameters = array())
    {
        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->all();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    protected function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * Get the UrlParameter key
     *
     * @return null|string
     */
    public function getKey()
    {
        return strval($this->getParameter('key')) ?: null;
    }

    /**
     * Set the UrlParameter key
     *
     * @param string $value
     * @return static
     */
    public function setKey($value)
    {
        return $this->setParameter('key', $value);
    }

    /**
     * Get the UrlParameter value
     *
     * @return null|string
     */
    public function getValue()
    {
        /**
         * @var null|string
         */
        return $this->getParameter('value');
    }

    /**
     * Set the UrlParameter value
     *
     * @param string $value
     * @return static
     */
    public function setValue($value)
    {
        return $this->setParameter('value', $value);
    }
}
