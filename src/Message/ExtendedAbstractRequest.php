<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\UrlParameterBag;

/**
 * BlueSnap Extended Abstract Request
 *
 * Adds additional functionality that is only needed by the Extended Payment API
 */
abstract class ExtendedAbstractRequest extends AbstractRequest
{
    /**
     * Gets the gateway's identifier for the store (BlueSnap calls this the store ID)
     *
     * @return string|null
     */
    public function getStoreReference()
    {
        return strval($this->getParameter('storeReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the store (BlueSnap calls this the store ID)
     *
     * @param string $value
     * @return static
     */
    public function setStoreReference($value)
    {
        return $this->setParameter('storeReference', $value);
    }

    /**
     * Gets the gateway's identifier for the plan (Bluesnap contract)
     *
     * @return string|null
     */
    public function getPlanReference()
    {
        return strval($this->getParameter('planReference')) ?: null;
    }

    /**
     * Sets the gateway's identifier for the plan (Bluesnap contract)
     *
     * @param string $value
     * @return static
     */
    public function setPlanReference($value)
    {
        return $this->setParameter('planReference', $value);
    }

    /**
     * Gets the parameters for the store URL
     *
     * @return UrlParameterBag|null
     */
    public function getStoreParameters()
    {
        /**
         * @var UrlParameterBag|null
         */
        return $this->getParameter('storeParameters');
    }

    /**
     * Sets the parameters for the store URL
     *
     * @param UrlParameterBag|array<mixed> $parameters
     * @return static
     */
    public function setStoreParameters($parameters)
    {
        if ($parameters && !$parameters instanceof UrlParameterBag) {
            $parameters = new UrlParameterBag($parameters);
        }

        return $this->setParameter('storeParameters', $parameters);
    }

    /**
     * Overriding to provide a more precise return type
     *
     * @return Response
     */
    public function send()
    {
        /**
         * @var Response
         */
        return parent::send();
    }
}
