<?php

namespace Omnipay\AuthorizeNetApi\Message\CustomerProfiles;

use Omnipay\AuthorizeNetApi\Message\AbstractResponse;
use Omnipay\AuthorizeNetApi\Message\CustomerProfiles\GetCustomerPaymentProfileRequest;

/**
 * @todo add more profile-specific accessors using getValue()
 */
class PaymentProfileResponse extends AbstractResponse
{
    public function __construct(GetCustomerPaymentProfileRequest $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, $data);
    }

    /**
     * @return string  Gateway-assigned ID for the customer shipping address, numeric string
     */
    public function getCardType()
    {
        return $this->getValue('paymentProfile.payment.creditCard.cardType');
    }
}
