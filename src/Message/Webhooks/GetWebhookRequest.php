<?php

namespace Omnipay\AuthorizeNetApi\Message\Webhooks;

use Academe\AuthorizeNet\Request\Model\Webhook;

/**
 * Request to get the details of a webhook
 *
 * @see https://developer.authorize.net/api/reference/features/webhooks.html#Get_a_Webhook
 */
class GetWebhookRequest extends AbstractWebhookRequest
{
    /**
     * Return the complete message object.
     * @return Webhook
     */
    public function getData()
    {
        $this->validate('webhookId');

        return $this->getWebhookId();
    }

    /**
     * Accept a webhook ID and send it as a request.
     *
     * @param string $data
     * @return WebhookResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(null, 'GET', '/webhooks/'.$data);
        return new WebhookResponse($this, $httpResponse->getBody()->getContents());
    }

    /**
     * @param string $value The ID for the webhook
     * @return self
     */
    public function setWebhookId($value)
    {
        return $this->setParameter('webhookId', $value);
    }

    /**
     * @return string
     */
    public function getWebhookId()
    {
        return $this->getParameter('webhookId');
    }
}
