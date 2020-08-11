<?php

namespace App\Hydrator;

use App\Entity\Category;

class CategoryHydrator implements HydratorInterface
{
    /**
     * @var Category
     */
    private $category;

    /**
     * @param Category $category
     */
    public function __construct(
        Category $category)
    {
        $this->category = $category;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->category->getId(),
            'name' => $this->category->getName(),
            'slug' => $this->category->getSlug(),
            'status' => $this->category->getStatus(),
            'documentaryCount' => $this->category->getDocumentaryCount()
        ];

        return $array;
    }

    public function toObject(array $data)
    {
        // TODO: Implement toObject() method.
    }
}