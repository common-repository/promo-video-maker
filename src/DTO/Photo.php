<?php

namespace Promo\DTO;

class Photo implements \JsonSerializable
{
    const PHOTO_SOURCE = 'wordpress';
    const PHOTO_TYPE = 'photo';
    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var string
     */
    private $url;
    
    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * @var array
     */
    private $meta;

    /**
     * @var string|bool
     */
    private $thumb;

    /**
     * @param \WP_Post $post
     * @param string $url
     * @param array $meta
     * @param string|bool $thumb
     */
    public function __construct(\WP_Post $post, string $url, string $downloadUrl, array $meta, $thumb)
    {
        $this->post = $post;
        $this->url = $url;
        $this->downloadUrl = $downloadUrl;
        $this->meta = $meta;
        $this->thumb = $thumb;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->post->ID,
            'height' => $this->meta['height'],
            'width' =>  $this->meta['width'],
            'source' => self::PHOTO_SOURCE,
            'type' => self::PHOTO_TYPE,
            'thumbnail_url' => $this->thumb ? $this->thumb : $this->url,
            'url' => $this->url,
            'download_url' => $this->downloadUrl,
        ];
    }
}
