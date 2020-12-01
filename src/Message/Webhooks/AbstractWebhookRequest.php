<?php

namespace Omnipay\AuthorizeNetApi\Message\Webhooks;

use Omnipay\AuthorizeNetApi\Message\AbstractRequest;

/**
 * Abstract request for the webhooks API - uses different endpoints/auth
 */
abstract class AbstractWebhookRequest extends AbstractRequest
{
    protected $endpointSandbox = 'https://apitest.authorize.net/rest/v1';
    protected $endpointLive = 'https://api.authorize.net/rest/v1';

    // @todo add some version that tacks on the method?
    // public function getEndpoint()
    // {
    //     return parent::getEndpoint();
    // }

    /**
     * Send a request to the gateway.
     *
     * Overrides {@see AbstractRequest::sendRequest()}:
     *  - new action parameter
     *  - authorization headers
     *
     * @param array  $data
     * @param string $method
     * @param string $action
     *
     * @return ResponseInterface
     */
    public function sendRequest($data = null, $method = 'POST', $action = '/webhooks')
    {
        $auth = $this->getAuth();
        $response = $this->httpClient->request(
            $method,
            $this->getEndpoint() . $action,
            array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(
                    $auth->getName() . ':' . $auth->getTransactionKey()
                ),
            ),
            json_encode($data)
        );

        return $response;
    }
}
