<?php

namespace App\Object\Activity\Data;

class AddedData implements Data
{
    /**
     * @var int
     */
    private $documentaryId;

    /**
     * @var string
     */
    private $documentaryTitle;

    /**
     * @var string
     */
    private $documentarySummary;

    /**
     * @var string
     */
    private $documentaryPoster;

    /**
     * @var string
     */
    private $documentarySlug;

    /**
     * @return int
     */
    public function getDocumentaryId(): int
    {
        return $this->documentaryId;
    }

    /**
     * @param int $documentaryId
     */
    public function setDocumentaryId(int $documentaryId): void
    {
        $this->documentaryId = $documentaryId;
    }

    /**
     * @return string
     */
    public function getDocumentaryTitle(): string
    {
        return $this->documentaryTitle;
    }

    /**
     * @param string $documentaryTitle
     */
    public function setDocumentaryTitle(string $documentaryTitle): void
    {
        $this->documentaryTitle = $documentaryTitle;
    }

    /**
     * @return string
     */
    public function getDocumentarySummary(): string
    {
        return $this->documentarySummary;
    }

    /**
     * @param string $documentarySummary
     */
    public function setDocumentarySummary(string $documentarySummary): void
    {
        $this->documentarySummary = $documentarySummary;
    }

    /**
     * @return string
     */
    public function getDocumentaryPoster(): string
    {
        return $this->documentaryPoster;
    }

    /**
     * @param string $documentaryPoster
     */
    public function setDocumentaryPoster(string $documentaryPoster): void
    {
        $this->documentaryPoster = $documentaryPoster;
    }

    /**
     * @return string
     */
    public function getDocumentarySlug(): string
    {
        return $this->documentarySlug;
    }

    /**
     * @param string $documentarySlug
     */
    public function setDocumentarySlug(string $documentarySlug): void
    {
        $this->documentarySlug = $documentarySlug;
    }

    public function toArray()
    {
        return [
            'documentaryId' => $this->documentaryId,
            'documentarySlug' => $this->documentarySlug,
            'documentaryTitle' => $this->documentaryTitle,
            'documentarySummary' => $this->documentarySummary,
            'documentaryPoster' => $this->documentaryPoster
        ];
    }
}