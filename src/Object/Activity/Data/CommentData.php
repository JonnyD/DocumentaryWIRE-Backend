<?php

namespace App\Object\Activity\Data;

class CommentData implements Data
{
    /**
     * @var int
     */
    private $commentId;

    /**
     * @var string
     */
    private $commentText;

    /**
     * @var int
     */
    private $documentaryId;

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
    public function getCommentId(): int
    {
        return $this->commentId;
    }

    /**
     * @param int $commentId
     */
    public function setCommentId(int $commentId): void
    {
        $this->commentId = $commentId;
    }

    /**
     * @return string
     */
    public function getCommentText(): string
    {
        return $this->commentText;
    }

    /**
     * @param string $commentText
     */
    public function setCommentText(string $commentText): void
    {
        $this->commentText = $commentText;
    }

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
            'commentId' => $this->commentId,
            'commentText' => $this->commentText,
            'documentaryId' => $this->documentaryId,
            'documentarySlug' => $this->documentarySlug,
            'documentaryPoster' => $this->documentaryPoster
        ];
    }
}