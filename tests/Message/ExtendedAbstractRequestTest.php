<?php

namespace Omnipay\BlueSnap\Message;

use Mockery;
use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use SimpleXMLElement;

class ExtendedAbstractRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var Mockery\MockInterface
     */
    protected $request;

    /**
     * @return void
     * @psalm-suppress TooManyArguments because Mockery is variadic
     */
    public function setUp()
    {
        $this->faker = new DataFaker();

        /**
         * @var Mockery\MockInterface
         */
        $this->request = Mockery::mock(
            '\Omnipay\BlueSnap\Message\ExtendedAbstractRequest'
        )->makePartial();
        $this->request->initialize();
    }

    /**
     * @return void
     */
    public function testStoreReference()
    {
        $storeReference = $this->faker->storeReference();
        $this->assertSame($this->request, $this->request->setStoreReference($storeReference));
        $this->assertSame($storeReference, $this->request->getStoreReference());
    }

    /**
     * @return void
     */
    public function testPlanReference()
    {
        $planReference = $this->faker->planReference();
        $this->assertSame($this->request, $this->request->setPlanReference($planReference));
        $this->assertSame($planReference, $this->request->getPlanReference());
    }

    /**
     * @return void
     */
    public function testStoreParameters()
    {
        $storeParameters = $this->faker->urlParameters();
        $this->assertSame($this->request, $this->request->setStoreParameters($storeParameters));
        $this->assertEquals($storeParameters, $this->request->getStoreParameters());
    }
}
