<?php

namespace Omnipay\BlueSnap;

use Omnipay\BlueSnap\Test\Framework\DataFaker;
use Omnipay\Tests\TestCase;

class CreditCardTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var CreditCard
     */
    protected $card;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->card = new CreditCard();
    }

    /**
     * @return void
     */
    public function testBrand()
    {
        $brand = $this->faker->cardBrand();
        $this->assertSame($this->card, $this->card->setBrand($brand));
        $this->assertSame($brand, $this->card->getBrand());
    }
}
