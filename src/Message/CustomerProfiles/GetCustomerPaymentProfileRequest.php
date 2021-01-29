<?php

namespace Omnipay\AuthorizeNetApi\Message\CustomerProfiles;

use Academe\AuthorizeNet\Request\GetCustomerPaymentProfile;
use Omnipay\AuthorizeNetApi\Message\AbstractRequest;

/**
 * Request to fetch the details of a customer payment profile
 *
 * @see https://developer.authorize.net/api/reference/index.html#customer-profiles-get-customer-payment-profile
 */
class GetCustomerPaymentProfileRequest extends AbstractRequest
{
    /**
     * Return the complete message object.
     * @return GetCustomerPaymentProfile
     */
    public function getData()
    {
        $this->validate('customerProfileId', 'customerPaymentProfileId');

        $request = new GetCustomerPaymentProfile(
            $this->getAuth(),
            $this->getCustomerProfileId(),
            $this->getCustomerPaymentProfileId()
        );

        if ($this->getTransactionId()) {
            $request = $request->withRefId($this->getTransactionId());
        }

        return $request;
    }

    /**
     * Accept payment profile details and send it as a request.
     *
     * @param GetCustomerPaymentProfile $data
     * @return PaymentProfileResponse
     */
    public function sendData($data)
    {
        $response_data = $this->sendMessage($data);

        return new PaymentProfileResponse($this, $response_data);
    }

    /**
     * @param string $value The gateway ID for the customer profile
     * @return self
     */
    public function setCustomerProfileId($value)
    {
        return $this->setParameter('customerProfileId', $value);
    }

    /**
     * @return string
     */
    public function getCustomerProfileId()
    {
        return $this->getParameter('customerProfileId');
    }

    /**
     * @param string $value The gateway ID for the customer payment profile
     * @return self
     */
    public function setCustomerPaymentProfileId($value)
    {
        return $this->setParameter('customerPaymentProfileId', $value);
    }

    /**
     * @return string
     */
    public function getCustomerPaymentProfileId()
    {
        return $this->getParameter('customerPaymentProfileId');
    }
}
