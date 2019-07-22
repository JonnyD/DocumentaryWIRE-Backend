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

class DocumentaryController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @param DocumentaryService $documentaryService
     * @param TokenStorageInterface $tokenStorage
     * @param ImageService $imageService
     * @param CategoryService $categoryService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        TokenStorageInterface $tokenStorage,
        ImageService $imageService,
        CategoryService $categoryService)
    {
        $this->documentaryService = $documentaryService;
        $this->tokenStorage = $tokenStorage;
        $this->imageService = $imageService;
        $this->categoryService = $categoryService;
    }

    /**
     * @FOSRest\Get("/documentary", name="get_documentary_admin_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function adminListAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse("Not granted");
        }

        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISH);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        $qb = $this->documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $data = [
            'items'             => $items,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return new JsonResponse($data, 200, array('Access-Control-Allow-Origin'=> '*'), array('Access-Control-Allow-Origin'=> '*'));
    }

    /**
     * @FOSRest\Get("/documentary/{slug}", name="get_documentary", options={ "method_prefix" = false })
     *
     * @param string $slug
     * @return Documentary|null
     */
    public function getDocumentaryAction(string $slug)
    {
        $documentary = $this->documentaryService->getDocumentaryBySlug($slug);

        return new JsonResponse($documentary, 200, array('Access-Control-Allow-Origin'=> '*'));
    }

    /**
     * @FOSRest\Patch("/documentary/{slug}", name="update_documentary", options={ "method_prefix" = false })
     *
     * @param string $slug
     * @param Request $request
     * @return Documentary|null
     */
    public function editDocumentaryAction(string $slug, Request $request)
    {
        $documentary = $this->documentaryService->getDocumentaryBySlug($slug);

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        $editDocumentaryForm = $this->createForm(EditDocumentaryForm::class, $documentary);
        $editDocumentaryForm->handleRequest($request);
        $editDocumentaryForm->submit($request->request->all(), false);
        $editedDocumentaryContent = json_decode($request->getContent(), true)['resource'];

        if ($editDocumentaryForm->isSubmitted() && $editDocumentaryForm->isValid()) {
            if (isset($editedDocumentaryContent['posterFile'])) {
                $posterFile = $editedDocumentaryContent['posterFile'];
                $outputFileWithoutExtension = $documentary->getSlug().'-'.uniqid();
                $path = 'uploads/posters/';
                $posterFileName = $this->imageService->saveBase54Image($posterFile, $outputFileWithoutExtension, $path);
                $documentary->setPosterFileName($posterFileName);
            }

            if (isset($editedDocumentaryContent['title'])) {
                $documentary->setTitle($editedDocumentaryContent['title']);
            }

            if (isset($editedDocumentaryContent['slug'])) {
                $documentary->setSlug($editedDocumentaryContent['slug']);
            }

            if (isset($editedDocumentaryContent['storyline'])) {
                $documentary->setStoryline($editedDocumentaryContent['storyline']);
            }

            if (isset($editedDocumentaryContent['summary'])) {
                $documentary->setSummary($editedDocumentaryContent['summary']);
            }

            if (isset($editedDocumentaryContent['year'])) {
                $documentary->setYear($editedDocumentaryContent['year']);
            }

            if (isset($editedDocumentaryContent['length'])) {
                $documentary->setLength($editedDocumentaryContent['length']);
            }

            if (isset($editedDocumentaryContent['short_url'])) {
                $documentary->setShortUrl($editedDocumentaryContent['short_url']);
            }

            if (isset($editedDocumentaryContent['status'])) {
                $documentary->setStatus($editedDocumentaryContent['status']);
            }

            if (isset($editedDocumentaryContent['wide_image'])) {
                $documentary->setWideImage($editedDocumentaryContent['wide_image']);
            }

            if (isset($editedDocumentaryContent['video_source'])) {
                $documentary->setVideoSource($editedDocumentaryContent['video_source']);
            }

            if (isset($editedDocumentaryContent['video_id'])) {
                $documentary->setVideoId($editedDocumentaryContent['video_id']);
            }

            if (isset($editedDocumentaryContent['featured'])) {
                $documentary->setFeatured($editedDocumentaryContent['featured']);
            }

            if (isset($editedDocumentaryContent['category_id'])) {
                $documentary->setCategory($editedDocumentaryContent['category_id']);
            }

            $this->documentaryService->save($documentary);
        }


        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        return new JsonResponse($documentary, 200, $headers);
    }


}