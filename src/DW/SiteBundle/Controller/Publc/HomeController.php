<?php

namespace DW\SiteBundle\Controller\Publc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('SiteBundle:Publc/Home:index.html.twig');
    }
}