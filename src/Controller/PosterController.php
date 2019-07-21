<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Order;
use App\Form\EditDocumentaryForm;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PosterController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @FOSRest\Post("/poster", name="upload_poster_for_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function uploadAction(Request $request)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('poster');

        $destination = $this->getParameter('kernel.project_dir')
            .'/public/uploads/posters';

        $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFileName = Urlizer::urlize($originalFileName).'-'.uniqid().'-'.$uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFileName
        );
    }
}