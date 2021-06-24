<?php

namespace Omnipay\AuthorizeNetApi\Message;

use Academe\AuthorizeNet\Request\AuthenticateTest;

class AuthenticateTestRequest extends AbstractRequest
{
    /**
     * Return the complete message object.
     */
    public function getData()
    {
        return [];
    }

    /**
     * Accept a subscription and send it as a request.
     *
     * @param array $data
     * @return Response
     */
    public function sendData($data)
    {
        $request = new AuthenticateTest($this->getAuth());
        $response_data = $this->sendMessage($request);

        return new Response($this, $response_data);
    }
}
