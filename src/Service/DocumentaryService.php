<?php

namespace App\Service;

use App\Entity\Documentary;
use App\Repository\DocumentaryRepository;

class DocumentaryService
{
    /**
     * @var DocumentaryRepository
     */
    private $documentaryRepository;

    /**
     * @param DocumentaryRepository $documentaryRepository
     */
    public function __construct(DocumentaryRepository $documentaryRepository)
    {
        $this->documentaryRepository = $documentaryRepository;
    }

    /**
     * @param string $slug
     * @return Documentary|null
     */
    public function getOneBySlug(string $slug)
    {
        return $this->documentaryRepository->findOneBy([
            "slug" => $slug
        ]);
    }
}