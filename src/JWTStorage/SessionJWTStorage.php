<?php

namespace Promo\JWTStorage;

use Promo\DTO\JWT;

/**
 * Requires the PHP session to be started before using the storage
 */
class SessionJWTStorage implements JWTStorage
{
    private const SESSION_TOKEN_KEY = 'promo_video_maker_jwt_token';

    /**
     * {@inheritDoc}
     */
    public function store(JWT $jwt): void
    {
        $_SESSION[self::SESSION_TOKEN_KEY] = $jwt->getToken();
    }

    /**
     * {@inheritDoc}
     */
    public function getJWT(): ?JWT
    {
        if (empty($_SESSION[self::SESSION_TOKEN_KEY])) {
            return null;
        }

        return new JWT($_SESSION[self::SESSION_TOKEN_KEY]);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        unset($_SESSION[self::SESSION_TOKEN_KEY]);
    }
}
