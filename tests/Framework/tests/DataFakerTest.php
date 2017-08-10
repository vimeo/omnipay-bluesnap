<?php

namespace Omnipay\BlueSnap\Test\Framework;

use Omnipay\Tests\TestCase;
use Omnipay\Common\Currency;
use DateTime;
use Omnipay\BlueSnap\UrlParameter;
use Omnipay\BlueSnap\UrlParameterBag;

class DataFakerTest extends TestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
    }

    /**
     * @return void
     */
    public function testIntBetween()
    {
        $int = $this->faker->intBetween(-3, 3);
        $this->assertTrue(is_int($int));
        $this->assertTrue(-3 <= $int);
        $this->assertTrue(3 >= $int);

        $int = $this->faker->intBetween(100, 200);
        $this->assertTrue(is_int($int));
        $this->assertTrue(100 <= $int);
        $this->assertTrue(200 >= $int);
    }

    /**
     * @return void
     */
    public function testBool()
    {
        $this->assertTrue(is_bool($this->faker->bool()));
    }

    /**
     * @return void
     */
    public function testMonetaryAmount()
    {
        $amount = $this->faker->monetaryAmount('USD');
        $this->assertTrue(is_string($amount));
        $this->assertEquals($amount, strval(floatval($amount)));
        $this->assertTrue($amount > 0);
        $parts = explode('.', $amount);
        $this->assertSame(2, count($parts));
        $this->assertSame(2, strlen($parts[1]));

        $amount = $this->faker->monetaryAmount('KRW');
        $this->assertTrue(is_string($amount));
        $this->assertTrue(ctype_digit($amount));
        $this->assertTrue($amount > 0);
    }

    /**
     * @return void
     */
    public function testCurrency()
    {
        $this->assertNotNull(Currency::find($this->faker->currency()));
    }

    /**
     * @return void
     */
    public function testName()
    {
        $name = $this->faker->name();
        $this->assertTrue(is_string($name));
        $this->assertTrue(strlen($name) > 0);
        $this->assertTrue(strpos($name, ' ') === false);
    }

    /**
     * @return void
     */
    public function testIpAddress()
    {
        $this->assertTrue(ip2long($this->faker->ipAddress()) !== false);
    }

    /**
     * @return void
     */
    public function testEmail()
    {
        $email = $this->faker->email();
        $this->assertTrue(is_string($email));

        $parts = explode('@example.', $email);
        $this->assertSame(2, count($parts));
        $this->assertTrue(0 < strlen($parts[0]));
        $this->assertSame(3, strlen($parts[1]));
    }

    /**
     * @return void
     */
    public function testUrl()
    {
        $url = $this->faker->url();
        $this->assertTrue(is_string($url));
        $this->assertSame(0, strpos($url, 'http://www.example.'));
        $this->assertTrue(strlen('http://www.example.') + 3 <= strlen($url));
    }

    /**
     * @return void
     */
    public function testQueryString()
    {
        $queryString = $this->faker->queryString();
        $this->assertTrue(is_string($queryString));
        parse_str($queryString, $parsedString);
        $this->assertTrue(is_array($parsedString));
        $this->assertTrue(count($parsedString) >= 1);
    }

    /**
     * @return void
     */
    public function testRegion()
    {
        $region = $this->faker->region();
        $this->assertTrue(is_string($region));
        $this->assertSame(2, strlen($region));
        $this->assertTrue(ctype_upper($region));
    }

    /**
     * @return void
     */
    public function testPostcode()
    {
        $postcode = $this->faker->postcode();
        $this->assertTrue(is_string($postcode));
        $this->assertSame(5, strlen($postcode));
        $this->assertTrue(ctype_digit($postcode));
    }

    /**
     * @return void
     */
    public function testRandomCharacters()
    {
        $length = $this->faker->intBetween(1, 100);
        $chars = $this->faker->randomCharacters('a', $length);
        $this->assertTrue(is_string($chars));
        $this->assertSame($length, strlen($chars));
        $this->assertSame(str_repeat('a', $length), $chars);

        $chars = $this->faker->randomCharacters('abc', $length);
        $this->assertSame($length, strlen($chars));
        $chars_array = str_split($chars);
        foreach ($chars_array as $char) {
            $this->assertTrue(in_array($char, array('a', 'b', 'c')));
        }
    }

    /**
     * @return void
     */
    public function testTimestamp()
    {
        $timestamp = $this->faker->timestamp();
        $this->assertTrue(is_string($timestamp));
        $this->assertTrue(strpos($timestamp, '00') === false);
        $datetime = new DateTime($timestamp);
        $this->assertSame($timestamp, $datetime->format('d-M-y'));
    }

    /**
     * @return void
     */
    public function testUsername()
    {
        $username = $this->faker->username();
        $this->assertTrue(is_string($username));
        $this->assertTrue(strlen($username) > 0);
    }

    /**
     * @return void
     */
    public function testPassword()
    {
        $password = $this->faker->password();
        $this->assertTrue(is_string($password));
        $this->assertTrue(strlen($password) > 0);
    }

    /**
     * @return void
     */
    public function testCustomerReference()
    {
        $reference = $this->faker->customerReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testTransactionReference()
    {
        $reference = $this->faker->transactionReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testSubscriptionReference()
    {
        $reference = $this->faker->subscriptionReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testSubscriptionChargeReference()
    {
        $reference = $this->faker->subscriptionChargeReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testStoreReference()
    {
        $reference = $this->faker->storeReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testPlanReference()
    {
        $reference = $this->faker->planReference();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 0);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testUrlParameter()
    {
        $urlParameter = $this->faker->urlParameter();
        $this->assertInstanceOf('Omnipay\BlueSnap\UrlParameter', $urlParameter);
        $this->assertTrue(is_string($urlParameter->getKey()));
        $this->assertTrue(is_string($urlParameter->getValue()));
    }

    /**
     * @return void
     */
    public function testUrlParameterAsArray()
    {
        $urlParameterArray = $this->faker->urlParameterAsArray();
        $urlParameter = new UrlParameter($urlParameterArray);
        $this->assertTrue(is_string($urlParameter->getKey()));
        $this->assertTrue(is_string($urlParameter->getValue()));
    }

    /**
     * @return void
     */
    public function testUrlParameters()
    {
        $urlParameters = $this->faker->urlParameters();
        $this->assertInstanceOf('Omnipay\BlueSnap\UrlParameterBag', $urlParameters);
        $this->assertTrue(0 < $urlParameters->count());
    }

    /**
     * @return void
     */
    public function testUrlParametersAsArray()
    {
        $urlParametersArray = $this->faker->urlParametersAsArray();
        $urlParameters = new UrlParameterBag($urlParametersArray);
        $this->assertTrue(0 < $urlParameters->count());
    }

    /**
     * @return void
     */
    public function testEncryptedUrlParameters()
    {
        $reference = $this->faker->encryptedUrlParameters();
        $this->assertTrue(is_string($reference));
        $this->assertTrue(strlen($reference) > 20);
        $this->assertNotEquals(0, $reference);
    }

    /**
     * @return void
     */
    public function testCustomParameterReference()
    {
        $parameter = $this->faker->customParameter();
        $this->assertTrue(is_string($parameter));
        $this->assertTrue(strlen($parameter) > 0);
        $this->assertNotEquals(0, $parameter);
    }

    /**
     * @return void
     */
    public function testCardBrand()
    {
        $brand = $this->faker->cardBrand();
        $this->assertTrue(is_string($brand));
        $this->assertTrue(strlen($brand) > 0);
        $card = new \Omnipay\Common\CreditCard();
        $brands = $card->getSupportedBrands();
        $this->assertTrue(isset($brands[$brand]));
    }

    /**
     * @return void
     */
    public function testCard()
    {
        $card = $this->faker->card();
        $this->assertInstanceOf('\Omnipay\BlueSnap\CreditCard', $card);
        $this->assertNull($card->validate());
    }
}
