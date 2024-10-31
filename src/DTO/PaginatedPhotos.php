<?php

namespace Promo\DTO;

class PaginatedPhotos implements \JsonSerializable
{
    /**
     * @var Photo[]
     */
    private $photos;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $totalPages;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * @param Photo[] $photos
     * @param int $currentPage
     * @param int $totalPages
     * @param int $totalCount
     */
    public function __construct(array $photos, int $currentPage, int $totalPages, int $totalCount)
    {
        $this->photos = $photos;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->totalCount = $totalCount;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        $data = [
            'photos' => $this->photos,
            'has_next_page' => $this->hasNextPage(),
            'has_previous_page' => $this->hasPreviousPage(),
        ];

        if ($this->hasNextPage()) {
            $data['cursor'] = $this->currentPage + 1;
        }

        return $data;
    }

    /**
     * @return bool
     */
    private function hasNextPage(): bool
    {
        return $this->totalPages > $this->currentPage;
    }

    /**
     * @return bool
     */
    private function hasPreviousPage(): bool
    {
        return $this->totalCount > 0 && $this->currentPage > 1;
    }
}
