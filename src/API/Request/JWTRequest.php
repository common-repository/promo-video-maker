<?php

namespace Promo\API\Request;

use JsonSerializable;

final class JWTRequest implements JsonSerializable
{
    /**
     * @var string
     */
    private $accessKey;

    /**
     * @param string $accessKey
     */
    public function __construct(string $accessKey)
    {
        $this->accessKey = $accessKey;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'token' => $this->accessKey,
            'reporting_token' => '',
        ];
    }
}
