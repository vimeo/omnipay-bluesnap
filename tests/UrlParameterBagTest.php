<?php

namespace Omnipay\BlueSnap;

use Omnipay\Tests\TestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;

class UrlParameterBagTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var UrlParameterBag
     */
    protected $bag;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var null|int|float|bool|string
     */
    protected $value;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->bag = new UrlParameterBag();
        $this->faker = new DataFaker();

        $parameter = $this->faker->urlParameter();
        $this->key = strval($parameter->getKey());
        $this->value = $parameter->getValue();
    }

    /**
     * @return void
     */
    public function testConstruct()
    {
        $bag = new UrlParameterBag(array(array(
            'key' => $this->key,
            'value' => $this->value
        )));
        $this->assertCount(1, $bag);
    }

    /**
     * Make sure all construction syntaxes return the same bag.
     *
     * @return void
     */
    public function testConstructSyntax()
    {
        $parameter2 = $this->faker->urlParameter();
        $key2 = $parameter2->getKey();
        $value2 = $parameter2->getValue();

        $bagFromArrays = new UrlParameterBag(array(
            array(
                'key' => $this->key,
                'value' => $this->value
            ),
            array(
                'key' => $key2,
                'value' => $value2
            )
        ));
        $bagFromObjects = new UrlParameterBag(array(
            new UrlParameter(array(
                'key' => $this->key,
                'value' => $this->value
            )),
            new UrlParameter(array(
                'key' => $key2,
                'value' => $value2
            )),
        ));
        $bagFromSimplifiedArray = new UrlParameterBag(array(
            $this->key => $this->value,
            $key2 => $value2
        ));

        $this->assertEquals($bagFromArrays, $bagFromObjects);
        $this->assertEquals($bagFromArrays, $bagFromSimplifiedArray);
    }

    /**
     * @return void
     */
    public function testAll()
    {
        $parameters = array(new UrlParameter(), new UrlParameter());
        $bag = new UrlParameterBag($parameters);

        $this->assertSame($parameters, $bag->all());
    }

    /**
     * @return void
     */
    public function testReplace()
    {
        $parameters = array(new UrlParameter(), new UrlParameter());
        $this->bag->replace($parameters);

        $this->assertSame($parameters, $this->bag->all());
    }

    /**
     * @return void
     */
    public function testAddWithUrlParameter()
    {
        $parameter = new UrlParameter();
        $parameter->setKey($this->key);
        $this->bag->add($parameter);

        $contents = $this->bag->all();
        $this->assertSame($parameter, $contents[0]);
    }

    /**
     * @return void
     */
    public function testAddWithArray()
    {
        $parameter = array(
            'key' => $this->key,
            'value' => $this->value
        );
        $this->bag->add($parameter);

        $contents = $this->bag->all();
        $this->assertInstanceOf('\Omnipay\BlueSnap\UrlParameter', $contents[0]);
        $this->assertSame($this->key, $contents[0]->getKey());
    }

    /**
     * @return void
     * @psalm-suppress MixedAssignment because we're testing that they're the same
     */
    public function testGetIterator()
    {
        $parameter = new UrlParameter();
        $parameter->setKey($this->key);
        $this->bag->add($parameter);

        foreach ($this->bag as $bagParameter) {
            $this->assertSame($parameter, $bagParameter);
        }
    }

    /**
     * @return void
     */
    public function testCount()
    {
        $count = $this->faker->intBetween(1, 5);
        for ($i = 0; $i < $count; $i++) {
            $this->bag->add(new UrlParameter());
        }
        $this->assertSame($count, count($this->bag));
    }
}
