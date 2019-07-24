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

        return new JsonResponse($data, 200, array('Access-Control-Allow-Origin'=> '*'));
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
        /** @var Documentary $documentary */
        $documentary = $this->documentaryService->getDocumentaryBySlug($slug);

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        $editDocumentaryForm = $this->createForm(EditDocumentaryForm::class, $documentary);
        $data = json_decode($request->getContent(), true)['resource'];
        $editDocumentaryForm->submit($data, false);

        if ($editDocumentaryForm->isSubmitted() && $editDocumentaryForm->isValid()) {
            $documentary = $this->mapArrayToObject($data, $documentary);
            $this->documentaryService->save($documentary);

            $this->documentaryService->save($documentary);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        return new JsonResponse($documentary, 200, $headers);
    }

    public function mapArrayToObject(array $data, Documentary $documentary)
    {
        if (isset($data['poster'])) {
            $poster = $data['poster'];
            $isBase64 = $this->imageService->isBase64($poster);
            if ($isBase64) {
                $outputFileWithoutExtension = $documentary->getSlug().'-'.uniqid();
                $path = 'uploads/documentary/posters/';
                $posterFileName = $this->imageService->saveBase54Image($poster, $outputFileWithoutExtension, $path);
                $documentary->setPosterFileName($posterFileName);
            }
        }

        if (isset($data['wide_image'])) {
            $wideImage = $data['wide_image'];
            $isBase64 = $this->imageService->isBase64($wideImage);
            if ($isBase64) {
                $outputFileWithoutExtension = $documentary->getSlug().'-'.uniqid();
                $path = 'uploads/documentary/wide/';
                $wideImageFileName = $this->imageService->saveBase54Image($wideImage, $outputFileWithoutExtension, $path);
                $documentary->setWideImage($wideImageFileName);
            }
        }

        if (isset($data['title'])) {
            $documentary->setTitle($data['title']);
        }

        if (isset($data['slug'])) {
            $documentary->setSlug($data['slug']);
        }

        if (isset($data['storyline'])) {
            $documentary->setStoryline($data['storyline']);
        }

        if (isset($data['summary'])) {
            $documentary->setSummary($data['summary']);
        }

        if (isset($data['year'])) {
            $documentary->setYear($data['year']);
        }

        if (isset($data['length'])) {
            $documentary->setLength($data['length']);
        }

        if (isset($data['short_url'])) {
            $documentary->setShortUrl($data['short_url']);
        }

        if (isset($data['status'])) {
            $documentary->setStatus($data['status']);
        }

        if (isset($data['video_source'])) {
            $documentary->setVideoSource($data['video_source']);
        }

        if (isset($data['video_id'])) {
            $documentary->setVideoId($data['video_id']);
        }

        if (isset($data['featured'])) {
            $documentary->setFeatured($data['featured']);
        }

        if (isset($data['category_id'])) {
            $documentary->setCategory($data['category_id']);
        }

        return $documentary;
    }
}