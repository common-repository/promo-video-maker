<?php

namespace Promo\JWTStorage;

use Promo\DTO\JWT;

interface JWTStorage
{
    /**
     * @param JWT $jwt
     */
    public function store(JWT $jwt): void;

    /**
     * @return JWT|null
     */
    public function getJWT(): ?JWT;

    /**
     * Removes the saved JWT from storage
     */
    public function clear(): void;
}
