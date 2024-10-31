<?php

namespace Promo\API;

use Promo\DTO\ConsumerKeys;

class GuzzleOAuthAPI implements OAuthAPI
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
    public function generateAccessToken(
        ConsumerKeys $consumerKeys,
        string $authorizationCode,
        string $redirectUrl
    ): string {
        $query = [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUrl,
        ];
        $uri = sprintf('/v1/oauth/token?%s', http_build_query($query));
        $response = $this->client->makeJSONRequest('POST', $uri, $consumerKeys);
        $responseBody = \GuzzleHttp\json_decode($response->getBody(), true);

        return $responseBody['access_token'];
    }
}
