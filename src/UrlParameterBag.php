<?php

namespace Omnipay\BlueSnap;

/**
 * UrlParameter Bag
 *
 * This class defines a bag (multi element set or array) of UrlParameters for a request.
 *
 * @see UrlParameter
 */
class UrlParameterBag implements \IteratorAggregate, \Countable
{
    /**
     * UrlParameter storage
     *
     * @var array<UrlParameter>
     */
    protected $parameters;

    /**
     * Constructor
     *
     * @param array<mixed> $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->replace($parameters);
    }

    /**
     * Return all the UrlParameters
     *
     * @return array<UrlParameter>
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Replace the contents of this bag with the specified UrlParameters.
     * $parameters can be an array of UrlParameters, an array of
     * `['key' => XXX, 'value' => XXX]` pairs, or an array of values
     * indexed by key.
     *
     * @param array<mixed> $parameters
     * @return void
     * @psalm-suppress MixedAssignment because we're accepting many different types of params
     */
    public function replace(array $parameters = array())
    {
        $this->parameters = array();

        foreach ($parameters as $key => $value) {
            if (is_array($value) || $value instanceof UrlParameter) {
                $this->add($value);
            } else {
                $this->add(array(
                    'key' => $key,
                    'value' => $value
                ));
            }
        }
    }

    /**
     * Add an UrlParameter to the bag
     * Can add an UrlParameter object or an associative array of UrlParameter
     * parameters (`['key' => XXX, 'value' => XXX]`)
     *
     * @param  UrlParameter|array<mixed> $parameter
     * @return void
     */
    public function add($parameter)
    {
        if ($parameter instanceof UrlParameter) {
            $this->parameters[] = $parameter;
        } else {
            $this->parameters[] = new UrlParameter($parameter);
        }
    }

    /**
     * Returns an iterator for UrlParameters
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of UrlParameters
     *
     * @return int
     */
    public function count()
    {
        return count($this->parameters);
    }
}
