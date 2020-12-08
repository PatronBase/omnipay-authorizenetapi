<?php

namespace Omnipay\AuthorizeNetApi\Message\RecurringBilling;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\AuthorizeNetApi\Message\AbstractResponse;

//@todo use abstract response?
//@todo subscription-specific accessors using getValue()
class SubscriptionResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, $data);
    }
}
