<?php

namespace Promo\DTO;

final class JWT
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $expiresAtTimestamp;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $segments = explode('.', $this->token);
        if (count($segments) !== 3) {
            throw new \InvalidArgumentException('invalid number of segments in JWT');
        }

        $data = json_decode(base64_decode($segments[1]), true);
        if ($data === null || empty($data['exp'])) {
            throw new \InvalidArgumentException('invalid JWT payload');
        }

        $this->expiresAtTimestamp = $data['exp'];
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getValidForSeconds(): int
    {
        return $this->expiresAtTimestamp - time();
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->getValidForSeconds() < 0;
    }
}
