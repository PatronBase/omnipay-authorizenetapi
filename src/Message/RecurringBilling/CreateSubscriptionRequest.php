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
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Number;
use Money\Parser\DecimalMoneyParser;
use Omnipay\AuthorizeNetApi\Message\AbstractRequest;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Request to create a subscription
 *
 * @see https://developer.authorize.net/api/reference/index.html#recurring-billing-create-a-subscription
 */
class CreateSubscriptionRequest extends AbstractRequest
{
    /**
     * Return the complete message object.
     * @return Subscription
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('amount', 'currency', 'intervalLength', 'intervalUnit', 'startDate', 'totalOccurrences');

        $amount = new Amount($this->getCurrency(), $this->getAmountInteger());

        $paymentSchedule = new PaymentSchedule(
            new Interval($this->getIntervalLength(), $this->getIntervalUnit()),
            $this->getStartDate(),
            $this->getTotalOccurrences(),
            $this->getTrialOccurrences()
        );

        $subscription = new Subscription(
            $paymentSchedule,
            $amount,
            null
        );

        if ($this->getName()) {
            $subscription = $subscription->withName($this->getName());
        }

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
            // @todo other payment methods
        }

        if ($subscription->getPayment() === null) {
            throw new InvalidRequestException('No valid payment method supplied');
        }

        if ($this->getTrialOccurrences() !== null && $this->getTrialAmountInteger() !== null) {
            $trialAmount = new Amount($this->getCurrency(), $this->getTrialAmountInteger());
            $subscription = $subscription->withTrialAmount($trialAmount);
        }

        if ($customer->hasAny()) {
            $subscription = $subscription->withCustomer($customer);
        }

        if ($this->getInvoiceNumber() || $this->getDescription()) {
            $order = new Order($this->getInvoiceNumber(), $this->getDescription());
            $subscription = $subscription->withOrder($order);
        }

        return $subscription;
    }

    /**
     * Accept a subscription and send it as a request.
     *
     * @param Subscription $data
     * @return SubscriptionResponse
     */
    public function sendData($data)
    {
        $request = (new CreateSubscription($this->getAuth(), $data))->with(['refId' => $this->getTransactionId()]);
        $response_data = $this->sendMessage($request);

        return new SubscriptionResponse($this, $response_data);
    }

    /**
     * @param string $value The name for the subscription, max 50 characters
     * @return self
     */
    public function setName($value)
    {
        return $this->setParameter('name', $value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getParameter('name');
    }

    /**
     * @param string $value The length of time between payments, in association with intervalUnit
     * @return self
     */
    public function setIntervalLength($value)
    {
        return $this->setParameter('intervalLength', $value);
    }

    /**
     * @return string
     */
    public function getIntervalLength()
    {
        return $this->getParameter('intervalLength');
    }

    /**
     * Value must be one of Interval::INTERVAL_UNIT_*
     * @param string $value The unit of time between payments, in association with intervalLength; 
     * @return self
     */
    public function setIntervalUnit($value)
    {
        return $this->setParameter('intervalUnit', $value);
    }

    /**
     * @return string
     */
    public function getIntervalUnit()
    {
        return $this->getParameter('intervalUnit');
    }

    /**
     * @param string $value The date the subscription is due to start
     * @return self
     */
    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    /**
     * @param int|string $value The number of payments in the subscription; maximum 4 digits
     * @return self
     */
    public function setTotalOccurrences($value)
    {
        return $this->setParameter('totalOccurrences', $value);
    }

    /**
     * @return string
     */
    public function getTotalOccurrences()
    {
        return (string) $this->getParameter('totalOccurrences');
    }

    /**
     * @param int|string $value The number of payments in the subscription trial period; maximum 2 digits
     * @return self
     */
    public function setTrialOccurrences($value)
    {
        return $this->setParameter('trialOccurrences', $value);
    }

    /**
     * @return string
     */
    public function getTrialOccurrences()
    {
        return (string) $this->getParameter('trialOccurrences');
    }


    /**
     * Copied from AbstractRequest::getMoney() - would be unnecessary if base function was protected instead of private
     *
     * @param  string|int|null $amount
     * @return null|Money
     * @throws InvalidRequestException
     */
    protected function getTrialMoney($amount = null)
    {
        $currencyCode = $this->getCurrency() ?: 'USD';
        $currency = new Currency($currencyCode);

        $amount = $amount !== null ? $amount : $this->getParameter('trialAmount');

        if ($amount === null) {
            return null;
        } elseif ($amount instanceof Money) {
            $money = $amount;
        } elseif (is_integer($amount)) {
            $money = new Money($amount, $currency);
        } else {
            $moneyParser = new DecimalMoneyParser($this->getCurrencies());

            $number = Number::fromString($amount);

            // Check for rounding that may occur if too many significant decimal digits are supplied.
            $decimal_count = strlen($number->getFractionalPart());
            $subunit = $this->getCurrencies()->subunitFor($currency);
            if ($decimal_count > $subunit) {
                throw new InvalidRequestException('Amount precision is too high for currency.');
            }

            $money = $moneyParser->parse((string) $number, $currency);
        }

        // Check for a negative amount.
        if (!$this->negativeAmountAllowed && $money->isNegative()) {
            throw new InvalidRequestException('A negative amount is not allowed.');
        }

        // Check for a zero amount.
        if (!$this->zeroAmountAllowed && $money->isZero()) {
            throw new InvalidRequestException('A zero amount is not allowed.');
        }

        return $money;
    }

    /**
     * Sets the trial payment amount.
     *
     * @param string|null $value
     * @return $this
     */
    public function setTrialAmount($value)
    {
        return $this->setParameter('trialAmount', $value !== null ? (string) $value : null);
    }

    /**
     * Validates and returns the formatted trial amount.
     *
     * @throws InvalidRequestException on any validation failure.
     * @return string The amount formatted to the correct number of decimal places for the selected currency.
     */
    public function getTrialAmount()
    {
        $money = $this->getTrialMoney();

        if ($money !== null) {
            $moneyFormatter = new DecimalMoneyFormatter($this->getCurrencies());

            return $moneyFormatter->format($money);
        }
    }

    /**
     * Sets the trial payment amount as integer.
     *
     * @param int $value
     * @return $this
     */
    public function setTrialAmountInteger($value)
    {
        return $this->setParameter('trialAmount', (int) $value);
    }

    /**
     * Get the payment amount as an integer.
     *
     * @return int
     */
    public function getTrialAmountInteger()
    {
        $money = $this->getTrialMoney();

        if ($money !== null) {
            return (int) $money->getAmount();
        }
    }
}
