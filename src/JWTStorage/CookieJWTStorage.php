<?php

namespace Promo\JWTStorage;

use Promo\DTO\JWT;

class CookieJWTStorage implements JWTStorage
{
    private const JWT_COOKIE_NAME = 'promoWordpressSession';

    /**
     * @var string
     */
    private $storeDomain;

    /**
     * @param string $storeDomain
     */
    public function __construct(string $storeDomain)
    {
        $this->storeDomain = $storeDomain;
    }

    /**
     * {@inheritDoc}
     */
    public function store(JWT $jwt): void
    {
        $this->setCookie(self::JWT_COOKIE_NAME, $jwt->getToken(), $jwt->getValidForSeconds());
    }

    /**
     * {@inheritDoc}
     */
    public function getJWT(): ?JWT
    {
        if (!isset($_COOKIE[self::JWT_COOKIE_NAME])) {
            return null;
        }

        return new JWT($_COOKIE[self::JWT_COOKIE_NAME]);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->setCookie(self::JWT_COOKIE_NAME, '', -1);
    }

    /**
     * @param string $name
     * @param string $value
     * @param int    $ttlSeconds
     */
    private function setCookie(string $name, string $value, int $ttlSeconds): void
    {
        setcookie($name, $value, time() + $ttlSeconds, '/', $this->storeDomain, true, false);
    }
}
