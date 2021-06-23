<?php

namespace Omnipay\AuthorizeNetApi\Message\Webhooks;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\AuthorizeNetApi\Message\AbstractResponse;

class WebhookResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, json_decode($data, true));
        // @todo parse $this->getData() into a Webhook model?
    }

    /**
     * @return bool  Whether the webhook was created or not
     */
    public function isSuccessful()
    {
        return $this->getWebhookId() !== null;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->isSuccessful() ? null : $this->getValue('message');
    }

    /**
     * Override accessor with simpler JSON valid-value lookup
     *
     * @param string $path
     * @return mixed|null
     */
    public function getValue($path)
    {
        return array_reduce(
            explode('.', $path),
            function ($o, $p) { return array_key_exists($p, $o) ? $o[$p] : null; },
            $this->getData()
        );
    }

    /**
     * @return string  Gateway-assigned ID for the webhook
     */
    public function getWebhookId()
    {
        return $this->getValue('webhookId');
    }

    /**
     * @return string  Merchant-assigned name of the webhook
     */
    public function getName()
    {
        return $this->getValue('name');
    }

    /**
     * @return string[]  Merchant-assigned set of events the webhook is subscribed to
     */
    public function getEventTypes()
    {
        return $this->getValue('eventTypes');
    }

    /**
     * @return string  Current active status of the webhook
     */
    public function getStatus()
    {
        return $this->getValue('status');
    }

    /**
     * @return string  Merchant-assigned notification URL of the webhook
     */
    public function getUrl()
    {
        return $this->getValue('url');
    }
}
