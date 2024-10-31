<?php

namespace Promo\API;

use Promo\API\Exception\PromoAPIException;
use Promo\DTO\ConsumerKeys;

interface OAuthAPI
{
    /**
     * @param ConsumerKeys $consumerKeys
     * @param string $authorizationCode
     * @param string $redirectUrl
     *
     * @return string
     *
     * @throws PromoAPIException
     */
    public function generateAccessToken(
        ConsumerKeys $consumerKeys,
        string $authorizationCode,
        string $redirectUrl
    ): string;
}
