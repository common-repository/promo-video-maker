<?php

namespace Promo\API;

use Promo\API\Exception\PromoAPIException;
use Promo\DTO\JWT;
use Promo\API\Request\JWTRequest;

interface AuthAPI
{
    /**
     * @param JWTRequest $request
     *
     * @return JWT
     *
     * @throws PromoAPIException
     */
    public function getJWT(JWTRequest $request): JWT;
}
