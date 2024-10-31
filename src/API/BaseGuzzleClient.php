<?php

namespace Promo\API;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Promo\API\Exception\PromoAPIException;
use Psr\Http\Message\ResponseInterface;

class BaseGuzzleClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $method
     * @param string $path
     * @param \JsonSerializable|null $body
     *
     * @return ResponseInterface
     *
     * @throws PromoAPIException
     */
    public function makeJSONRequest(string $method, string $path, ?\JsonSerializable $body): ResponseInterface
    {
        try {
            $response = $this->client->request(
                $method,
                ltrim($path, '/'),
                [
                    RequestOptions::JSON => $body,
                    RequestOptions::HEADERS => [
                        'accept' => 'application/json',
                    ],
                ]
            );
        } catch (GuzzleException $e) {
            throw new PromoAPIException('got an error when calling Promo API', 0, $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw new PromoAPIException((string)$response->getBody());
        }

        return $response;
    }

    /**
     * @param string $baseUrl
     * @param bool $ignoreSslErrors
     *
     * @return static
     */
    public static function withParams(string $baseUrl, bool $ignoreSslErrors): self
    {
        $client = new Client(
            [
                'base_uri' => rtrim($baseUrl, '/') . '/',
                'verify' => !$ignoreSslErrors,
            ]
        );

        return new static($client);
    }
}
