<?php

namespace Omnipay\AuthorizeNetApi\Message\RecurringBilling;

use Academe\AuthorizeNet\Request\Model\Interval;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\Request\Model\Subscription;
use Money\Currency;
use Money\Money;
use Omnipay\Tests\TestCase;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use ReflectionClass;

class CreateSubscriptionRequestTest extends TestCase
{
    /** @var CreateSubscriptionRequest */
    protected $request;

    public function setUp()
    {
        parent::setUp();
        
        $this->request = new CreateSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize([
            "authName" => "5KP3u95bQpv",
            "transactionKey" => "346HZ32z3fP4hTG2",
            "transactionId" => "123456",

            "subscriptionName" => "Sample subscription",
            "intervalLength" => "1",
            "intervalUnit" => Interval::INTERVAL_UNIT_MONTHS,
            "startDate" => "2020-08-30",
            "totalOccurrences" => "12",
            "currency" => "USD",
            "amount" => "10.29",
            "card" => new CreditCard([
                "number" => "4111111111111111",
                "expiryYear" => "2020",
                "expiryMonth" => "12",
            ]),
        ]);
    }

    public function testBasics()
    {
        $subscription = $this->request->getData();
        $this->assertSame(Subscription::class, get_class($subscription));
        $this->assertSame("Sample subscription", $subscription->getName());
        $this->assertSame("1", $subscription->getPaymentSchedule()->getInterval()->getLength());
        $this->assertSame("months", $subscription->getPaymentSchedule()->getInterval()->getUnit());
        $this->assertSame("2020-08-30", $subscription->getPaymentSchedule()->getStartDate());
        $this->assertSame("12", $subscription->getPaymentSchedule()->getTotalOccurrences());
        $this->assertSame("USD", $subscription->getAmount()->getCurrencyCode());
        $this->assertSame("10.29", $subscription->getAmount()->getFormatted());
        $this->assertSame("2020-12", $subscription->getPayment()->getExpirationDate());
        $this->assertSame("4111111111111111", $subscription->getPayment()->getCardNumber());
        $this->assertSame("1111", $subscription->getPayment()->getLastFourDigits());
        
        $this->assertNull($subscription->getPaymentSchedule()->getTrialOccurrences());
        $this->assertNull($subscription->getTrialAmount());
        $this->assertNull($subscription->getPayment()->getCardCode());
        $this->assertNull($subscription->getBillTo());
        $this->assertNull($subscription->getShipTo());
    }
    
    public function testFull()
    {
        $this->request->initialize([
            "authName" => "5KP3u95bQpv",
            "transactionKey" => "346HZ32z3fP4hTG2",
            "transactionId" => "123456",
            
            "name" => "Sample subscription",
            "intervalLength" => "1",
            "intervalUnit" => "months",
            "startDate" => "2020-08-30",
            "totalOccurrences" => "12",
            "trialOccurrences" => "1",
            "currency" => "USD",
            "amount" => "10.29",
            "trialAmount" => "0.00",
            "card" => new CreditCard([
                "number" => "4111111111111111",
                "expiryYear" => "2020",
                "expiryMonth" => "12",
                "firstName" => "John",
                "lastName" => "Smith",
                "email" => "john.smith@example.com",
            ]),
            "invoiceNumber" => "MERCH1234567890",
            "description" => "Sample merchant description",
        ]);
        $subscription = $this->request->getData();
        
        $this->assertSame("1", $subscription->getPaymentSchedule()->getTrialOccurrences());
        $this->assertSame("0.00", $subscription->getTrialAmount()->getFormatted());
        $this->assertSame("john.smith@example.com", $subscription->getCustomer()->getEmail());
        $this->assertSame("MERCH1234567890", $subscription->getOrder()->getInvoiceNumber());
        $this->assertSame("Sample merchant description", $subscription->getOrder()->getDescription());

        $this->assertInstanceOf(NameAddress::class, $subscription->getBillTo());
        $this->assertInstanceOf(NameAddress::class, $subscription->getShipTo());
    }

    public function testTrialAmountFormats()
    {
        $this->request->setTrialAmount(10);
        $this->assertSame('10.00', $this->request->getTrialAmount());

        $this->request->setTrialAmount(new Money(1234, new Currency('USD')));
        $this->assertSame('12.34', $this->request->getTrialAmount());

        $this->request->setTrialAmount(null);
        $this->assertNull($this->request->getTrialAmount());
        $this->assertNull($this->request->getTrialAmountInteger());

        $this->request->setTrialAmountInteger(1000);
        $this->assertSame('10.00', $this->request->getTrialAmount());
    }


    public function testTrialPrecisionError()
    {
        $this->request->setTrialAmount('10.258');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Amount precision is too high for currency.');

        $this->request->getTrialAmount();
    }

    public function testNegativeTrialError()
    {
        $this->request->setTrialAmount('-1.53');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('A negative amount is not allowed.');

        $this->request->getTrialAmount();
    }

    public function testZeroTrialError()
    {
        $this->request->setTrialAmount('0.00');
        // workaround to set protected property from outside class without inheritance
        $reflection = new ReflectionClass($this->request);
        $reflectionProperty = $reflection->getProperty('zeroAmountAllowed');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->request, false);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('A zero amount is not allowed.');

        $this->request->getTrialAmount();
    }

    public function testNoPaymentError()
    {
        $this->request->setCard(null);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('No valid payment method supplied');

        $this->request->getData();
    }
}
