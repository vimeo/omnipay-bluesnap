<?php

namespace Omnipay\BlueSnap;

use Omnipay\Tests\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class UrlParameterTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var UrlParameter
     */
    protected $parameter;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $value;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->parameter = new UrlParameter();
        $this->faker = new DataFaker();

        $parameter = $this->faker->urlParameter();
        $key = $parameter->getKey();
        $value = $parameter->getValue();
        // this should always be true
        if ($key !== null && $value !== null) {
            $this->key = $key;
            $this->value = $value;
        }
    }

    /**
     * @return void
     */
    public function testConstructWithParams()
    {
        $parameter = new UrlParameter(array(
            'key' => $this->key,
            'value' => $this->value
        ));
        $this->assertSame($this->key, $parameter->getKey());
        $this->assertSame($this->value, $parameter->getValue());
    }

    /**
     * @return void
     */
    public function testInitializeWithParams()
    {
        $this->assertSame($this->parameter, $this->parameter->initialize(array(
            'key' => $this->key,
            'value' => $this->value
        )));
        $this->assertSame($this->key, $this->parameter->getKey());
        $this->assertSame($this->value, $this->parameter->getValue());
    }

    /**
     * @return void
     */
    public function testGetParameters()
    {
        $this->assertSame($this->parameter, $this->parameter->setKey($this->key)->setValue($this->value));
        $this->assertSame(array('key' => $this->key, 'value' => $this->value), $this->parameter->getParameters());
    }

    /**
     * @return void
     */
    public function testKey()
    {
        $this->assertSame($this->parameter, $this->parameter->setKey($this->key));
        $this->assertSame($this->key, $this->parameter->getKey());
    }

    /**
     * @return void
     */
    public function testValue()
    {
        $this->assertSame($this->parameter, $this->parameter->setValue($this->value));
        $this->assertSame($this->value, $this->parameter->getValue());
    }
}
