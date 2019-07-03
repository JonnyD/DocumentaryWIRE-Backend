<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryController extends AbstractController
{
    public function __invoke($slug, CategoryService $categoryService)
    {
        return $slug;
        //return $categoryService->getCategoryBySlug($slug);
    }

    public function show($slug, CategoryService $categoryService)
    {
        return $slug;
        //return $categoryService->getCategoryBySlug($slug);
    }
}