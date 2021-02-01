<?php

namespace Omnipay\AuthorizeNetApi\Message\CustomerProfiles;

use Omnipay\AuthorizeNetApi\Message\CustomerProfiles\PaymentProfileResponse;
use Omnipay\Tests\TestCase;

class PaymentProfileResponseTest extends TestCase
{
    /** @var PaymentProfileResponse */
    protected $response;

    public function testCreditCard()
    {
        $this->response = new PaymentProfileResponse(
            $this->getMockRequest(),
            [
                "paymentProfile" => [
                    "customerProfileId" => "39598611",
                    "customerPaymentProfileId" => "35936989",
                    "payment" => [
                        "creditCard" => [
                            "cardNumber" => "XXXX1111",
                            "expirationDate" => "XXXX",
                            "cardType" => "Visa",
                            "issuerNumber" => "411111",
                            "isPaymentToken" => true
                        ]
                    ],
                    "subscriptionIds" => [
                        "3078153",
                        "3078154"
                    ],
                    "customerType" => "individual",
                    "billTo" => [
                        "firstName" => "John",
                        "lastName" => "Smith"
                    ]
                ],
                "messages" => [
                    "resultCode" => "Ok",
                    "message" => [
                        [
                            "code" => "I00001",
                            "text" => "Successful."
                        ]
                    ]
                ]
            ]
        );

        $this->assertTrue($this->response->isSuccessful());
        $this->assertFalse($this->response->isRedirect());
        $this->assertNull($this->response->getTransactionId());
        $this->assertSame("Successful.", $this->response->getMessage());
        $this->assertSame("Visa", $this->response->getCardType());
    }

    // @todo add tests for 'bankaccount' version

    // @todo test failure cases
    // @todo test invalid response data
}
