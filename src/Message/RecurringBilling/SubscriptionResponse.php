<?php

namespace Omnipay\AuthorizeNetApi\Message\RecurringBilling;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\AuthorizeNetApi\Message\AbstractResponse;

class SubscriptionResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, $data);
    }

    /**
     * @return string  Gateway-assigned ID for the customer shipping address, numeric string
     */
    public function getCustomerAddressId()
    {
        return $this->getValue('profile.customerAddressId');
    }

    /**
     * @return string  Gateway-assigned ID for the customer payment profile, numeric string
     */
    public function getCustomerPaymentProfileId()
    {
        return $this->getValue('profile.customerPaymentProfileId');
    }

    /**
     * @return string  Gateway-assigned ID for the customer profile, numeric string
     */
    public function getCustomerProfileId()
    {
        return $this->getValue('profile.customerProfileId');
    }

    /**
     * @return string  Gateway-assigned ID for the subscription, numeric string up to 13 digits
     */
    public function getSubscriptionId()
    {
        return $this->getValue('subscriptionId');
    }
}
