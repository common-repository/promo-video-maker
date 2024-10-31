<?php

namespace Promo\JWTStorage;

use Promo\DTO\JWT;

class TransientJWTStorage implements JWTStorage
{
    private const TRANSIENT_TOKEN_KEY = 'promo_video_maker_jwt_token';

    /**
     * {@inheritDoc}
     */
    public function store(JWT $jwt): void
    {
        set_transient(self::TRANSIENT_TOKEN_KEY, $jwt->getToken(), $jwt->getValidForSeconds());
    }

    /**
     * {@inheritDoc}
     */
    public function getJWT(): ?JWT
    {
        $token = get_transient(self::TRANSIENT_TOKEN_KEY);
        if (!$token) {
            return null;
        }

        return new JWT($token);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        delete_transient(self::TRANSIENT_TOKEN_KEY);
    }
}
