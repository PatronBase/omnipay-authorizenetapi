<?php

namespace Omnipay\AuthorizeNetApi\Message\CustomerProfiles;

use Academe\AuthorizeNet\Request\GetCustomerPaymentProfile;
use Omnipay\AuthorizeNetApi\Message\CustomerProfiles\GetCustomerPaymentProfileRequest;
use Omnipay\Tests\TestCase;

class GetCustomerPaymentProfileRequestTest extends TestCase
{
    /** @var GetCustomerPaymentProfileRequest */
    protected $request;

    public function setUp()
    {
        parent::setUp();
        
        $this->request = new GetCustomerPaymentProfileRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize([
            "authName" => "5KP3u95bQpv",
            "transactionKey" => "346HZ32z3fP4hTG2",
            "transactionId" => "123456",

            "customerProfileId" => "10000",
            "customerPaymentProfileId" => "20000",
        ]);
    }

    public function testRequest()
    {
        $profile_request = $this->request->getData();

        $this->assertInstanceOf(GetCustomerPaymentProfile::class, $profile_request);
        $this->assertSame("123456", $profile_request->getRefId());
        $this->assertSame("10000", $profile_request->getCustomerProfileId());
        $this->assertSame("20000", $profile_request->getCustomerPaymentProfileId());
    }
}
