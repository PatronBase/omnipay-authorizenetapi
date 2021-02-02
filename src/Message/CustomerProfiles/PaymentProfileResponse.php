<?php

namespace Omnipay\AuthorizeNetApi\Message\CustomerProfiles;

use Academe\AuthorizeNet\Payment\CreditCard;
use Omnipay\AuthorizeNetApi\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * @todo add more profile-specific accessors using getValue()
 */
class PaymentProfileResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, $data);
    }

    /**
     * @return string|null  Card number if payment is a credit card, or null if payment is bank account
     */
    public function getCardNumber()
    {
        $payment = $this->getValue('paymentProfile.payment');
        if ($payment instanceof CreditCard) {
            return $payment->getCardNumber();
        }
    }

    /**
     * @return string|null  Card type if payment is a credit card, or null if payment is bank account
     */
    public function getCardType()
    {
        $payment = $this->getValue('paymentProfile.payment');
        if ($payment instanceof CreditCard) {
            return $payment->getCardType();
        }
    }

    /**
     * @return string|null  Expiration date if payment is a credit card, or null if payment is bank account
     */
    public function getExpirationDate()
    {
        $payment = $this->getValue('paymentProfile.payment');
        if ($payment instanceof CreditCard) {
            return $payment->getExpirationDate();
        }
    }
}
