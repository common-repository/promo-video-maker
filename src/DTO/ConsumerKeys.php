<?php

namespace Promo\DTO;

final class ConsumerKeys implements \JsonSerializable
{
    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * @param string $consumerKey
     * @param string $consumerSecret
     */
    public function __construct(string $consumerKey, string $consumerSecret)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * @return string
     */
    public function getConsumerKey(): string
    {
        return $this->consumerKey;
    }

    /**
     * @return string
     */
    public function getConsumerSecret(): string
    {
        return $this->consumerSecret;
    }

    /**
     * @return bool
     */
    public function areEmpty(): bool
    {
        return empty($this->consumerKey) && empty($this->consumerSecret);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'client_id' => $this->consumerKey,
            'client_secret' => $this->consumerSecret,
        ];
    }
}
