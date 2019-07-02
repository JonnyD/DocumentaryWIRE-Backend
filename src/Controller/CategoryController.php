<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryController extends AbstractController
{


    public function show($slug, CategoryService $categoryService)
    {
        return $categoryService->getCategoryBySlug($slug);
    }
}