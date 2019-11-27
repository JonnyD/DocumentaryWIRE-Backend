<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\Episodic;
use App\Entity\Poster;
use App\Entity\Standalone;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\DocumentaryType;
use App\Enum\Order;
use App\Form\AdminDocumentaryForm;
use App\Form\EpisodicForm;
use App\Form\StandaloneForm;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use App\Service\ImageService;
use App\Service\UserService;
use App\Service\VideoSourceService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
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

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param DocumentaryService $documentaryService
     * @param UserService $userService
     * @param TokenStorageInterface $tokenStorage
     * @param ImageService $imageService
     * @param CategoryService $categoryService
     * @param VideoSourceService $videoSourceService
     * @param RequestStack $requestStack
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     */
    public function __construct(
        DocumentaryService $documentaryService,
        UserService $userService,
        TokenStorageInterface $tokenStorage,
        ImageService $imageService,
        CategoryService $categoryService,
        VideoSourceService $videoSourceService,
        RequestStack $requestStack)
    {
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
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

        if (!$isRoleAdmin) {
            $criteria->setStatus(DocumentaryStatus::PUBLISH);
        }

        $type = $request->query->get('type');
        if (isset($type)) {
            $criteria->setType($type);
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

        $addedBy = $request->query->get('addedBy');
        if (isset($addedBy)) {
            $user = $this->userService->getUserByUsername($addedBy);
            $criteria->setAddedBy($user);
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
        /** @var Documentary $item */
        foreach ($items as $item) {
            if ($item->isStandalone()) {
                $serialized[] = $this->serializeStandalone($item);
            } else if ($item->isEpisodic()) {
                $serialized[] = $this->serializeEpisodic($item);
            } else {
                //@TODO throw exception
            }
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

        if ($documentary->isStandalone()) {
            $serialized = $this->serializeStandalone($documentary);
        } else if ($documentary->isEpisodic()) {
            $serialized = $this->serializeEpisodic($documentary);
        }

        return new JsonResponse($serialized, 200, $headers);
    }

    /**
     * @FOSRest\Post("/documentary", name="create_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createDocumentaryAction(Request $request)
    {
        $type = $request->query->get('type');

        if ($type === DocumentaryType::STANDALONE) {
            $documentary = new Standalone();
            $formClass = StandaloneForm::class;
        } else if ($type === DocumentaryType::EPISODIC) {
            $documentary = new Episodic();
            $formClass = EpisodicForm::class;
        } else {
            //@TODO throw exception
        }

        $documentary->setType($type);
        $documentary->setStatus(DocumentaryStatus::PENDING);

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        $documentary->setAddedBy($this->getLoggedInUser());

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $form = $this->createForm($formClass, $documentary);

        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $documentary->setType($type);
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->mapArrayToObject($data, $documentary);
                $this->documentaryService->save($documentary);
                if ($documentary->isStandalone()) {
                    $serialized = $this->serializeStandalone($documentary);
                } else if ($documentary->isEpisodic()) {
                    $serialized = $this->serializeEpisodic($documentary);
                }
                return new JsonResponse($serialized, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 400, $headers);
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

        $form = $this->createForm(StandaloneForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true)['resource'];
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->documentaryService->save($documentary);
                if ($documentary->isStandalone()) {
                    $serialized = $this->serializeStandalone($documentary);
                } else if ($documentary->isEpisodic()) {
                    $serialized = $this->serializeEpisodic($documentary);
                }
                return new JsonResponse($serialized, 200, $headers);
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
            $outputFileWithoutExtension = uniqid();
            $path = 'uploads/posters/';

            $isBase64 = $this->imageService->isBase64($poster);
            $isUrl = $this->imageService->isUrl($poster);
            if ($isBase64) {
                $posterFileName = $this->imageService->saveBase54Image($poster, $outputFileWithoutExtension, $path);
                $documentary->setPosterFileName($posterFileName);
            } else if ($isUrl) {
                $posterFileName = $this->imageService->saveFromURL($poster, $path);
                $documentary->setPosterFileName($posterFileName);
            }
        }

        if (isset($data['wideImage'])) {
            $wideImage = $data['wideImage'];
            $outputFileWithoutExtension = uniqid();
            $path = 'uploads/wide/';

            $isBase64 = $this->imageService->isBase64($wideImage);
            $isUrl = $this->imageService->isUrl($wideImage);
            if ($isBase64) {
                $wideImageFileName = $this->imageService->saveBase54Image($wideImage, $outputFileWithoutExtension, $path);
                $documentary->setWideImage($wideImageFileName);
            } else if ($isUrl) {
                $wideImageFileName = $this->imageService->saveFromURL($wideImage, $path);
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
    private function serializeStandalone(Documentary $documentary)
    {
        $standalone = $documentary->getStandalone();

        $serialized = [
            'id' => $documentary->getId(),
            'type' => $documentary->getType(),
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
            'imdbId' => $documentary->getImdbId(),
            'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPosterFileName(),
            'wideImage' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/wide/' . $documentary->getWideImage(),
            'category' => [
                'id' => $documentary->getCategory()->getId(),
                'name' => $documentary->getCategory()->getName(),
                'slug' => $documentary->getCategory()->getSlug()
            ]
        ];

        if ($documentary->getAddedBy() != null) {
            $serialized['addedBy'] = [
                'username' => $documentary->getAddedBy()->getUsername()
            ];
        }

        if ($standalone->getVideoSource() != null) {
            $serialized['videoSource'] = [
                'id' => $standalone->getVideoSource()->getId(),
                'name' => $standalone->getVideoSource()->getName()
            ];
        }

        if ($standalone->getVideoId() != null) {
            $serialized['videoId'] = $standalone->getVideoId();
        }

        return $serialized;
    }

    /**
     * @param Documentary $documentary
     * @return array
     */
    private function serializeEpisodic(Documentary $documentary)
    {
        $episodic = $documentary->getEpisodic();

        $serialized = [
            'id' => $episodic->getId(),
            'type' => $documentary->getType(),
            'title' => $documentary->getTitle(),
            'slug' => $documentary->getSlug(),
            'storyline' => $documentary->getStoryline(),
            'summary' => $documentary->getSummary(),
            'year' => $documentary->getYear(),
            'status' => $documentary->getStatus(),
            'views' => $documentary->getViews(),
            'shortUrl' => $documentary->getShortUrl(),
            'featured' => $documentary->getFeatured(),
            'imdbId' => $documentary->getImdbId(),
            'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPosterFileName(),
            'wideImage' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/wide/' . $documentary->getWideImage(),
            'category' => [
                'id' => $documentary->getCategory()->getId(),
                'name' => $documentary->getCategory()->getName(),
                'slug' => $documentary->getCategory()->getSlug()
            ]
        ];

        if ($documentary->getAddedBy() != null) {
            $serialized['addedBy'] = [
                'username' => $documentary->getAddedBy()->getUsername()
            ];
        }

        $seasonsArray = [];
        foreach ($episodic->getSeasons() as $season) {

            $episodesArray = [];
            foreach ($season->getEpisodes() as $episode) {
                $episodesArray[] = [
                    'number' => $episode->getNumber()
                ];
            }

            $seasonArray = [];
            $seasonArray[] = [
                'number' => $season->getNumber(),
                'episodes' => $episodesArray
            ];

            $seasonsArray[] = $seasonArray;
        }

        $serialized['seasons'] = $seasonsArray;

        return $serialized;
    }

    /**
     * @return User
     */
    private function getLoggedInUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}