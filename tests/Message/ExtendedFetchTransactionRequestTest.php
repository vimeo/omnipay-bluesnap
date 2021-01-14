<?php

namespace Omnipay\BlueSnap\Message;

use Omnipay\BlueSnap\Test\Framework\OmnipayBlueSnapTestCase;
use Omnipay\BlueSnap\Test\Framework\DataFaker;
use DateTime;

class ExtendedFetchTransactionRequestTest extends OmnipayBlueSnapTestCase
{
    /**
     * @var DataFaker
     */
    protected $faker;

    /**
     * @var ExtendedFetchTransactionRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $transactionReference;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->faker = new DataFaker();
        $this->transactionReference = $this->faker->transactionReference();

        $this->request = new ExtendedFetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransactionReference($this->transactionReference);
    }

    /**
     * @return void
     */
    public function testEndpoint()
    {
        $this->assertSame(
            'https://sandbox.bluesnap.com/services/2/orders/resolve?invoiceId=' . strval($this->transactionReference),
            $this->request->getEndpoint()
        );
    }

    /**
     * @return void
     */
    public function testHttpMethod()
    {
        $this->assertSame('GET', $this->request->getHttpMethod());
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $this->assertNull($this->request->getData());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The transactionReference parameter is required
     * @return void
     * @psalm-suppress NullArgument we're wiping it out for testing purposes
     */
    public function testGetDataTransactionRequired()
    {
        $this->request->setTransactionReference(null);
        $this->request->getData();
    }

    /**
     * @return void
     */
    public function testSendSuccess()
    {
        $customerReference = $this->faker->customerReference();
        $planReference = $this->faker->planReference();
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $tax = $this->faker->monetaryAmount($currency);
        $dateCreated = $this->faker->timestamp();
        $fakeCard = $this->faker->card();
        $cardBrand = $fakeCard->getBrand();
        $cardLastFour = $fakeCard->getNumberLastFour();
        $expiryMonth = (string) $fakeCard->getExpiryMonth();
        $expiryYear = (string) $fakeCard->getExpiryYear();
        $firstName = $fakeCard->getFirstName();
        $lastName = $fakeCard->getLastName();
        $email = $fakeCard->getEmail();
        $state = $fakeCard->getState();
        $country = $fakeCard->getCountry();
        $postcode = $fakeCard->getPostcode();
        $status = $this->faker->transactionStatus();
        $custom1_name = $this->faker->name();
        $custom1_value = $this->faker->customParameter();
        $custom2_name = $this->faker->name();
        $custom2_value = $this->faker->customParameter();

        $this->setMockHttpResponse('ExtendedFetchTransactionSuccess.txt', array(
            'TRANSACTION_REFERENCE' => $this->transactionReference,
            'CUSTOMER_REFERENCE' => $customerReference,
            'PLAN_REFERENCE' => $planReference,
            'AMOUNT' => $amount,
            'TAX' => $tax,
            'CURRENCY' => $currency,
            'DATE_CREATED' => $dateCreated,
            'CARD_LAST_FOUR' => $cardLastFour,
            'CARD_BRAND' => ucfirst($cardBrand ?: ''),
            'EXPIRY_MONTH' => $expiryMonth,
            'EXPIRY_YEAR' => $expiryYear,
            'FIRST_NAME' => $firstName,
            'LAST_NAME' => $lastName,
            'EMAIL' => $email,
            'STATE' => $state,
            'COUNTRY' => $country,
            'POSTCODE' => $postcode,
            'STATUS' => $status,
            'CUSTOM_1_NAME' => $custom1_name,
            'CUSTOM_1_VALUE' => $custom1_value,
            'CUSTOM_2_NAME' => $custom2_name,
            'CUSTOM_2_VALUE' => $custom2_value
        ));
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame($this->transactionReference, $response->getTransactionReference());
        $this->assertSame($customerReference, $response->getCustomerReference());
        $this->assertSame($currency, $response->getCurrency());
        $this->assertSame($amount, $response->getAmount());
        $this->assertSame($tax, $response->getTax());
        $this->assertSame($status, $response->getStatus());
        $this->assertSame($custom1_value, $response->getCustomParameter($custom1_name));
        $this->assertSame($custom2_value, $response->getCustomParameter($custom2_name));
        $responseDateCreated = $response->getDateCreated();
        $this->assertEquals(new DateTime($dateCreated), $responseDateCreated);
        $this->assertEquals(
            'Etc/GMT+8',
            $responseDateCreated ? $responseDateCreated->getTimezone()->getName() : null
        );
        $card = $response->getCard();
        $this->assertInstanceOf('\Omnipay\BlueSnap\CreditCard', $card);
        if ($card === null) {
            // so psalm knows the things below won't be hit
            return;
        }
        $this->assertSame($cardLastFour, $card->getNumberLastFour());
        $this->assertSame($cardBrand, $card->getBrand());
        $this->assertSame($expiryMonth, (string) $card->getExpiryMonth());
        $this->assertSame($expiryYear, (string) $card->getExpiryYear());
        $this->assertSame($firstName, $card->getFirstName());
        $this->assertSame($lastName, $card->getLastName());
        $this->assertSame($email, $card->getEmail());
        $this->assertSame($country, $card->getCountry());
        $this->assertSame($postcode, $card->getPostcode());
        $this->assertNull($response->getMessage());
    }

    /**
     * Tests when the response returned includes multiple invoices
     *
     * @return void
     */
    public function testSendMultipleInvoicesSuccess()
    {
        $customerReference = $this->faker->customerReference();
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $dateCreated = $this->faker->timestamp();

        $this->setMockHttpResponse('ExtendedFetchTransactionMultipleInvoicesSuccess.txt', array(
            'TRANSACTION_REFERENCE' => $this->transactionReference,
            'CUSTOMER_REFERENCE' => $customerReference,
            'AMOUNT' => $amount,
            'CURRENCY' => $currency,
            'DATE_CREATED' => $dateCreated
        ));
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('200', $response->getCode());
        $this->assertSame($this->transactionReference, $response->getTransactionReference());
        $this->assertSame($customerReference, $response->getCustomerReference());
        $this->assertSame($currency, $response->getCurrency());
        $this->assertSame($amount, $response->getAmount());
        $responseDateCreated = $response->getDateCreated();
        $this->assertEquals(new DateTime($dateCreated), $responseDateCreated);
        $this->assertEquals(
            'Etc/GMT+8',
            $responseDateCreated ? $responseDateCreated->getTimezone()->getName() : null
        );
        $this->assertNull($response->getMessage());
    }

    /**
     * @return void
     */
    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExtendedFetchTransactionFailure.txt', array(
            'TRANSACTION_REFERENCE' => $this->transactionReference
        ));
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
        $this->assertSame(
            'Order retrieval service failure. Order ID: ' . $this->transactionReference . ' is not found.',
            $response->getMessage()
        );
    }

    /**
     * @psalm-suppress PossiblyNullReference
     * @return void
     */
    public function testGetTransaction()
    {
        $currency = $this->faker->currency();
        $amount = $this->faker->monetaryAmount($currency);
        $customer_reference = $this->faker->customerReference();
        $date_created = $this->faker->timestamp();
        $status = $this->faker->transactionStatus();

        $this->setMockHttpResponse('ExtendedFetchTransactionSuccess.txt', array(
            'AMOUNT' => $amount,
            'DATE_CREATED' => $date_created,
            'CURRENCY' => $currency,
            'CUSTOMER_REFERENCE' => $customer_reference,
            'TRANSACTION_REFERENCE' => $this->transactionReference,
            'STATUS' => $status,
        ));

        $response = $this->request->send();
        $transaction = $response->getTransaction();
        $this->assertSame($amount, $transaction->getAmount());
        $this->assertSame($currency, $transaction->getCurrency());
        $this->assertSame($status, $transaction->getStatus());
        $this->assertSame($customer_reference, $transaction->getCustomerReference());
        $this->assertSame($date_created, $transaction->getDate()->format('d-M-y'));
        $this->assertSame($this->transactionReference, $transaction->getTransactionReference());
    }
}
