<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Order;
use App\Form\EditDocumentaryForm;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use App\Service\ImageService;
use App\Service\VideoSourceService;
use App\Utils\Base64FileExtractor;
use App\Utils\UploadedBase64File;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Hshn\Base64EncodedFile\HttpFoundation\File\Base64EncodedFile;
use Symfony\Component\HttpFoundation\File\File;

class VideoSourceController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var VideoSourceService
     */
    private $videoService;

    /**
     * @param VideoSourceService $videoService
     */
    public function __construct(VideoSourceService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * @FOSRest\Get("/video-source", name="get_video_sources", options={ "method_prefix" = false })
     */
    public function getVideoSourcesAction()
    {
        $videoSources = $this->videoService->getAllVideoSources();

        $formatted = [];
        foreach ($videoSources as $videoSource) {
            $formatted[] = $videoSource->jsonSerialize();
        }

        return new JsonResponse($formatted, 200,  array('Access-Control-Allow-Origin'=> '*'));
    }
}