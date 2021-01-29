<?php

namespace Omnipay\AuthorizeNetApi;

use Omnipay\AuthorizeNetApi\Message\CustomerProfiles\PaymentProfileResponse;
use Omnipay\AuthorizeNetApi\Message\RecurringBilling\SubscriptionResponse;
use Omnipay\Tests\GatewayTestCase;

class ApiGatewayTests extends GatewayTestCase
{
    /** @var ApiGateway */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new ApiGateway(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->gateway->setAuthName('5KP3u95bQpv');
        $this->gateway->setTransactionKey('346HZ32z3fP4hTG2');
        // @todo reference ID shouldn't be set on the gateway as it's a transaction-specific parameter?
        // $this->gateway->setRefId('123456');
    }

    // @todo add authorize/purchase tests

    public function testCreateSubscription()
    {
        $response = $this->gateway->createSubscription([
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
            "card" => $this->getValidCard(),
        ])->send();

        $this->assertInstanceOf(SubscriptionResponse::class, $response);

        // response tests
        // $this->assertSame('', $response->getCustomerAddressId());
        // $this->assertSame('', $response->getCustomerProfileId());
        // $this->assertSame('', $response->getCustomerPaymentProfileId());
        // $this->assertSame('', $response->getCustomerSubscriptionId());
    }

    public function testGetCustomerPaymentProfile()
    {
        $response = $this->gateway->getCustomerPaymentProfile([
            "transactionId" => "123456",
            "customerProfileId" => "10000",
            "customerPaymentProfileId" => "20000",
        ])->send();

        $this->assertInstanceOf(PaymentProfileResponse::class, $response);

        // response tests
        // $this->assertSame('', $response->getCardType());
    }
}
