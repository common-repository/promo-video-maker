<?php

namespace Promo\API;

use Promo\API\Request\JWTRequest;
use Promo\DTO\JWT;

class GuzzleAuthAPI implements AuthAPI
{
    /**
     * @var BaseGuzzleClient
     */
    private $client;

    /**
     * @param BaseGuzzleClient $client
     */
    public function __construct(BaseGuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function getJWT(JWTRequest $request): JWT
    {
        $response = $this->client->makeJSONRequest('POST', '/v1/external/promo', $request);
        $responseBody = \GuzzleHttp\json_decode($response->getBody(), true);

        return new JWT($responseBody['token']);
    }
}
