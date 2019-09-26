<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Order;
use App\Form\AdminDocumentaryForm;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use App\Service\ImageService;
use App\Service\VideoSourceService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Gedmo\Sluggable\Util\Urlizer;
use PhpParser\Comment\Doc;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var VideoSourceService
     */
    private $videoSourceService;

    private $reguest;

    /**
     * @param DocumentaryService $documentaryService
     * @param TokenStorageInterface $tokenStorage
     * @param ImageService $imageService
     * @param CategoryService $categoryService
     * @param VideoSourceService $videoSourceService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        TokenStorageInterface $tokenStorage,
        ImageService $imageService,
        CategoryService $categoryService,
        VideoSourceService $videoSourceService,
        RequestStack $requestStack)
    {
        $this->documentaryService = $documentaryService;
        $this->tokenStorage = $tokenStorage;
        $this->imageService = $imageService;
        $this->categoryService = $categoryService;
        $this->videoSourceService = $videoSourceService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/documentary", name="get_documentary_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin) {
            $criteria->setStatus(DocumentaryStatus::PUBLISH);
        }

        if ($isRoleAdmin) {
            $videoSourceId = $request->query->get('videoSource');
            if (isset($videoSourceId)) {
                $videoSource = $this->videoSourceService->getVideoSourceById($videoSourceId);
                $criteria->setVideoSource($videoSource);
            }

            $status = $request->query->get('status');
            if (isset($status)) {
                $criteria->setStatus($status);
            }

            $featured = $request->query->get('featured');
            if (isset($featured)) {
                $featured = $featured === 'true' ? true: false;
                $criteria->setFeatured($featured);
            }
        }

        $categorySlug = $request->query->get('category');
        if (isset($categorySlug)) {
            $category = $this->categoryService->getCategoryBySlug($categorySlug);
            $criteria->setCategory($category);
        }

        $year = $request->query->get('year');
        if (isset($year)) {
            $criteria->setYear($year);
        }

        $duration = $request->query->get('duration');
        if (isset($duration)) {
            $criteria->setDuration($duration);
        }

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        } else {
            $criteria->setSort([
                DocumentaryOrderBy::CREATED_AT => Order::DESC
            ]);
        }

        $amountPerPage = $request->query->get('amountPerPage', 12);
        if (isset($amountPerPage) && $amountPerPage > 50) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $qb = $this->documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($amountPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $serialized[] = $this->serializeDocumentary($item);
        }

        $data = [
            'items'             => $serialized,
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

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        $serializedDocumentary = $this->serializeDocumentary($documentary);

        return new JsonResponse($serializedDocumentary, 200, $headers);
    }

    /**
     * @FOSRest\Post("/documentary", name="create_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createDocumentaryAction(Request $request)
    {
        $documentary = new Documentary();

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];
        $form = $this->createForm(AdminDocumentaryForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->mapArrayToObject($data, $documentary);
                $this->documentaryService->save($documentary);
                $serializedDocumentary = $this->serializeDocumentary($documentary);
                return new JsonResponse($serializedDocumentary, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 200, $headers);
            }
        }
    }

    /**
     * @FOSRest\Patch("/documentary/{id}", name="partial_update_documentary", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editDocumentaryAction(int $id, Request $request)
    {
        /** @var Documentary $documentary */
        $documentary = $this->documentaryService->getDocumentaryById($id);

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $form = $this->createForm(AdminDocumentaryForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true)['resource'];
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->documentaryService->save($documentary);
                $serializedDocumentary = $this->serializeDocumentary($documentary);
                return new JsonResponse($serializedDocumentary, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 200, $headers);
            }
        }
    }

    /**
     * @param array $data
     * @param Documentary $documentary
     * @return Documentary
     */
    public function mapArrayToObject(array $data, Documentary $documentary)
    {
        if (isset($data['poster'])) {
            $poster = $data['poster'];
            $isBase64 = $this->imageService->isBase64($poster);
            if ($isBase64) {
                $outputFileWithoutExtension = $documentary->getSlug().'-'.uniqid();
                $path = 'uploads/posters/';
                $posterFileName = $this->imageService->saveBase54Image($poster, $outputFileWithoutExtension, $path);
                $documentary->setPosterFileName($posterFileName);
            }
        }

        if (isset($data['wideImage'])) {
            $wideImage = $data['wideImage'];
            $isBase64 = $this->imageService->isBase64($wideImage);
            if ($isBase64) {
                $outputFileWithoutExtension = $documentary->getSlug().'-'.uniqid();
                $path = 'uploads/wide/';
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

        if (isset($data['shortUrl'])) {
            $documentary->setShortUrl($data['shortUrl']);
        }

        if (isset($data['status'])) {
            $documentary->setStatus($data['status']);
        }

        if (isset($data['videoSource'])) {
            $videoSource = $this->videoSourceService->getVideoSourceById($data['videoSource']);
            $documentary->setVideoSource($videoSource);
        }

        if (isset($data['videoId'])) {
            $documentary->setVideoId($data['videoId']);
        }

        if (isset($data['featured'])) {
            $documentary->setFeatured($data['featured']);
        }

        if (isset($data['category'])) {
            $category = $this->categoryService->getCategoryById($data['category']);
            $documentary->setCategory($category);
        }

        return $documentary;
    }

    /**
     * @param Documentary $documentary
     * @return array
     */
    private function serializeDocumentary(Documentary $documentary)
    {
        $serialized = [
            'id' => $documentary->getId(),
            'title' => $documentary->getTitle(),
            'slug' => $documentary->getSlug(),
            'storyline' => $documentary->getStoryline(),
            'summary' => $documentary->getSummary(),
            'year' => $documentary->getYear(),
            'length' => $documentary->getLength(),
            'status' => $documentary->getStatus(),
            'views' => $documentary->getViews(),
            'shortUrl' => $documentary->getShortUrl(),
            'featured' => $documentary->getFeatured(),
            'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPosterFileName(),
            'wideImage' => $documentary->getWideImage(),
            'category' => [
                'id' => $documentary->getCategory()->getId(),
                'name' => $documentary->getCategory()->getName(),
                'slug' => $documentary->getCategory()->getSlug()
            ],
            'videoSource' => [
                'id' => $documentary->getVideoSource()->getId(),
                'name' => $documentary->getVideoSource()->getName()
            ],
            'videoId' => $documentary->getVideoId()
        ];

        return $serialized;
    }
}