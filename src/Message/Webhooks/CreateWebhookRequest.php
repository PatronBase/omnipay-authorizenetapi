<?php

namespace Omnipay\AuthorizeNetApi\Message\Webhooks;

use Academe\AuthorizeNet\Request\Model\Webhook;

/**
 * Request to create a webhook
 *
 * @see https://developer.authorize.net/api/reference/features/webhooks.html#Create_A_Webhook
 */
class CreateWebhookRequest extends AbstractWebhookRequest
{
    /**
     * Return the complete message object.
     * @return Webhook
     */
    public function getData()
    {
        $this->validate('notifyUrl', 'eventTypes');

        $webhook = new Webhook($this->getNotifyUrl(), $this->getEventTypes());

        if ($this->getName()) {
            $webhook = $webhook->withName($this->getName());
        }
        // @todo consider adding `elseif ($this->getDescription())` block

        if ($this->getStatus()) {
            $webhook = $webhook->withStatus($this->getStatus());
        }

        return $webhook;
    }

    /**
     * Accept a webhook and send it as a request.
     *
     * @param Webhook $data
     * @return WebhookResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest($data);
        return new WebhookResponse($this, $httpResponse->getBody()->getContents());
    }

    /**
     * @param string $value The name for the webhook
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
     * @param string[] $value The event types the webhook should be subscribed to
     * @return self
     */
    public function setEventTypes($value)
    {
        return $this->setParameter('eventTypes', $value);
    }

    /**
     * @return string[]
     */
    public function getEventTypes()
    {
        return $this->getParameter('eventTypes');
    }

    /**
     * @param string $value Whether the webhook is 'active' or 'inactive'
     * @return self
     */
    public function setStatus($value)
    {
        return $this->setParameter('status', $value);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getParameter('status');
    }
}
