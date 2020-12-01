<?php

namespace Omnipay\AuthorizeNetApi\Message\RecurringBilling;

use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\Payment\CreditCard;
use Academe\AuthorizeNet\Request\CreateSubscription;
use Academe\AuthorizeNet\Request\Model\Customer;
use Academe\AuthorizeNet\Request\Model\Interval;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\Request\Model\PaymentSchedule;
use Academe\AuthorizeNet\Request\Model\Subscription;
use Omnipay\AuthorizeNetApi\Message\AbstractRequest;

// @todo replace all the copied content from CaptureRequest with new objects from fork
class CreateSubscriptionRequest extends AbstractRequest
{
    /**
     * Return the complete message object.
     */
    public function getData()
    {
        $amount = new Amount($this->getCurrency(), $this->getAmountInteger());


        // DEBUG VALUES
        // @todo needs accessor
        $interval = new Interval(30, Interval::INTERVAL_UNIT_DAYS);
        // @todo needs accessor
        $paymentSchedule = new PaymentSchedule($interval, "2020-09-30", 11, 1);
        // @todo needs accessor
        $trialAmount = new Amount($this->getCurrency(), "0");
        // @todo needs accessor
        $order = new Order("MERCH1234567890", "Sample merchant description");
        // $customer = new Customer(Customer::CUSTOMER_TYPE_INDIVIDUAL, "CUSTOMER123456", "john.smith@example.com");

        // @todo new subscription (punt to below card check rather than base+with() pattern?)
        $subscription = new Subscription(
            $paymentSchedule,
            $amount,
            $payment,
            "Sample subscription",
            $trialAmount,
            $order,
            $customer,
            $billTo,
            $shipTo
        );


        // Build the customer, and add the customer to the transaction
        // if it has any attributes set.
        $customer = new Customer($this->getCustomerType(), $this->getCustomerId());

        $card = $this->getCard();
        if ($card) {
            if ($card->getEmail()) {
                $customer = $customer->withEmail($card->getEmail());
            }

            $billingAddress = trim($card->getBillingAddress1().' '.$card->getBillingAddress2());

            if ($billingAddress === '') {
                $billingAddress = null;
            }

            $billTo = new NameAddress(
                $card->getBillingFirstName(),
                $card->getBillingLastName(),
                $card->getBillingCompany(),
                $billingAddress,
                $card->getBillingCity(),
                $card->getBillingState(),
                $card->getBillingPostcode(),
                $card->getBillingCountry()
            );

            // The billTo may have phone and fax number, but the shipTo does not.
            $billTo = $billTo->with(['phoneNumber' => $card->getBillingPhone(), 'faxNumber' => $card->getBillingFax()]);

            if ($billTo->hasAny()) {
                $subscription = $subscription->withBillTo($billTo);
            }

            $shippingAddress = trim($card->getShippingAddress1().' '.$card->getShippingAddress2());

            if ($shippingAddress === '') {
                $shippingAddress = null;
            }

            $shipTo = new NameAddress(
                $card->getShippingFirstName(),
                $card->getShippingLastName(),
                $card->getShippingCompany(),
                $shippingAddress,
                $card->getShippingCity(),
                $card->getShippingState(),
                $card->getShippingPostcode(),
                $card->getShippingCountry()
            );

            if ($shipTo->hasAny()) {
                $subscription = $subscription->withShipTo($shipTo);
            }

            // A credit card has been supplied.
            if ($card->getNumber()) {

                $card->validate();

                $creditCard = new CreditCard($card->getNumber(), $card->getExpiryDate('Y-m'));

                if ($card->getCvv()) {
                    $creditCard = $creditCard->withCardCode($card->getCvv());
                }

                $subscription = $subscription->withPayment($creditCard);
            }
        }

        if ($customer->hasAny()) {
            $subscription = $subscription->withCustomer($customer);
        }





        return $subscription;
    }

    /**
     * Accept a subscription and send it as a request.
     *
     * @param Subscription $data
     * @return SubscriptionResponse {@todo new response model?}
     */
    public function sendData($data)
    {
        $request = (new CreateSubscription($this->getAuth(), $data))->with(['refId' => $this->getTransactionId()]);
        $response_data = $this->sendMessage($request);

        return new SubscriptionResponse($this, $response_data);
    }
}
