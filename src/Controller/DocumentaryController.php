<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Movie;
use App\Entity\Poster;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\DocumentaryType;
use App\Enum\Order;
use App\Form\AdminDocumentaryForm;
use App\Form\DocumentaryMovieForm;
use App\Form\DocumentarySeriesForm;
use App\Form\SeriesForm;
use App\Form\MovieForm;
use App\Service\ActivityService;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Criteria\DocumentaryCriteria;
use App\Service\DocumentaryVideoSourceService;
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
use Symfony\Component\Form\FormError;
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

class DocumentaryController extends BaseController implements ClassResourceInterface
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
     * @var DocumentaryVideoSourceService
     */
    private $documentaryVideoSourceService;

    /**
     * @var ActivityService
     */
    private $activityService;

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
     * @param DocumentaryVideoSourceService $documentaryVideoSourceService
     * @param ActivityService $activityService
     * @param RequestStack $requestStack
     */
    public function __construct(
        DocumentaryService $documentaryService,
        UserService $userService,
        TokenStorageInterface $tokenStorage,
        ImageService $imageService,
        CategoryService $categoryService,
        VideoSourceService $videoSourceService,
        DocumentaryVideoSourceService $documentaryVideoSourceService,
        ActivityService $activityService,
        RequestStack $requestStack)
    {
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
        $this->tokenStorage = $tokenStorage;
        $this->imageService = $imageService;
        $this->categoryService = $categoryService;
        $this->videoSourceService = $videoSourceService;
        $this->documentaryVideoSourceService = $documentaryVideoSourceService;
        $this->activityService = $activityService;
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

        $yearFrom = $request->query->get('yearFrom');
        if (isset($yearFrom)) {
            $criteria->setYear($yearFrom);
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

        $amountPerPage = $request->query->get('amountPerPage', 20);
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
            if ($item->isMovie()) {
                $serialized[] = $this->serializeMovie($item);
            } else if ($item->isSeries()) {
                $serialized[] = $this->serializeSeries($item);
            } else {
                throw new \Exception();
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

        return $this->createApiResponse($data, 200);
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

        $this->documentaryService->updateViews($documentary);

        if ($documentary->isMovie()) {
            $serialized = $this->serializeMovie($documentary);
        } else if ($documentary->isSeries()) {
            $serialized = $this->serializeSeries($documentary);
        }

        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Post("/documentary/movie", name="create_movie_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createMovieocumentaryAction(Request $request)
    {
        $documentary = new Documentary();

        $movie = new Movie();
        $documentary->setMovie($movie);
        $documentary->setType(DocumentaryType::MOVIE);
        $documentary->setStatus(DocumentaryStatus::PENDING);
        $documentary->setAddedBy($this->getLoggedInUser());

        $form = $this->createForm(DocumentaryMovieForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $poster = $data['poster'];
            if ($poster == null) {
                $formError = new FormError("Poster is required");
                $form->addError($formError);
            }

            $wideImage = $data['wideImage'];
            if ($wideImage == null) {
                $formError = new FormError("Wide image is required");
                $form->addError($formError);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapMovieImages($documentary, $data);

                $documentaryVideoSources = $this->documentaryVideoSourceService
                    ->addDocumentaryVideoSourcesFromMovieDocumentary($data['movie'], $documentary);
                $documentary->setDocumentaryVideoSources($documentaryVideoSources);

                $this->documentaryService->save($documentary);

                $serialized = $this->serializeMovie($documentary);
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }

    /**
     * @FOSRest\Post("/documentary/series", name="create_series_ocumentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createSeriesDocumentaryAction(Request $request)
    {
        $documentary = new Documentary();

        $series = new Series();
        $documentary->setSeries($series);
        $documentary->setType(DocumentaryType::SERIES);
        $documentary->setAddedBy($this->getLoggedInUser());
        $documentary->setStatus(DocumentaryStatus::PENDING);

        $form = $this->createForm(DocumentarySeriesForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapSeriesImages($documentary, $data);

                $seasons = $documentary->getSeries()->getSeasons()->toArray();
                $documentaryVideoSources = $this->documentaryVideoSourceService
                    ->addDocumentaryVideoSourcesFroSeriesDocumentary($seasons, $documentary);
                $documentary->setDocumentaryVideoSources($documentaryVideoSources);

                $this->documentaryService->save($documentary);

                $serialized = $this->serializeSeries($documentary);
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400,);
            }
        }
    }

    /**
     * @FOSRest\Patch("/documentary/movie/{id}", name="partial_update_movie_documentary", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editMovieDocumentaryAction(int $id, Request $request)
    {
        /** @var Documentary $documentary */
        $documentary = $this->documentaryService->getDocumentaryById($id);
        $oldCategory = $documentary->getCategory();

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        if (!$documentary->isMovie()) {
            //@todo

            throw new \Exception();
        }

        $form = $this->createForm(DocumentaryMovieForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapMovieImages($documentary, $data);

                $documentaryVideoSources = $this->documentaryVideoSourceService
                    ->addDocumentaryVideoSourcesFromMovieDocumentary($data['movie'], $documentary);
                $documentary->setDocumentaryVideoSources($documentaryVideoSources);

                $this->documentaryService->save($documentary);

                $newCategory = $documentary->getCategory();
                if ($oldCategory->getId() != $newCategory->getId()) {
                    $oldCategory->removeDocumentary($documentary);
                    $this->categoryService->updateDocumentaryCountForCategory($oldCategory);
                }

                $this->categoryService->updateDocumentaryCountForCategory($newCategory);

                $serialized = $this->serializeMovie($documentary);
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
            }
        }

    }

    /**
     * @FOSRest\Patch("/documentary/series/{id}", name="partial_update_series_documentary", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editSeriesDocumentaryAction(int $id, Request $request)
    {
        /** @var Documentary $documentary */
        $documentary = $this->documentaryService->getDocumentaryById($id);

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        if (!$documentary->isSeries()) {
            //@todo
            return null;
        }

        $form = $this->createForm(DocumentarySeriesForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapSeriesImages($documentary, $data);

                $seasons = $documentary->getSeries()->getSeasons()->toArray();
                $documentaryVideoSources = $this->documentaryVideoSourceService
                    ->addDocumentaryVideoSourcesFroSeriesDocumentary($seasons, $documentary);
                $documentary->setDocumentaryVideoSources($documentaryVideoSources);

                $this->documentaryService->save($documentary);

                $serialized = $this->serializeSeries($documentary);
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
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
    private function serializeMovie(Documentary $documentary)
    {
        $movie = $documentary->getMovie();

        $serialized = [
            'id' => $documentary->getId(),
            'type' => $documentary->getType(),
            'title' => $documentary->getTitle(),
            'slug' => $documentary->getSlug(),
            'storyline' => $documentary->getStoryline(),
            'summary' => $documentary->getSummary(),
            'year' => $documentary->getYearFrom(),
            'length' => $documentary->getLength(),
            'status' => $documentary->getStatus(),
            'views' => $documentary->getViews(),
            'shortUrl' => $documentary->getShortUrl(),
            'featured' => $documentary->getFeatured(),
            'imdbId' => $documentary->getImdbId(),
            'category' => [
                'id' => $documentary->getCategory()->getId(),
                'name' => $documentary->getCategory()->getName(),
                'slug' => $documentary->getCategory()->getSlug()
            ],
            'createdAt' => $documentary->getCreatedAt(),
            'updatedAt' => $documentary->getUpdatedAt()
        ];

        if ($documentary->getPoster() != null) {
            $serialized['poster'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/posters/' . $documentary->getPoster();
        } else {
            $serialized['poster'] = null;
        }

        if ($documentary->getWideImage() != null) {
           $serialized['wideImage'] = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/wide/' . $documentary->getWideImage();
        } else {
            $serialized['wideImage'] = null;
        }

        if ($documentary->getAddedBy() != null) {
            $serialized['addedBy'] = [
                'username' => $documentary->getAddedBy()->getUsername()
            ];
        }

        if ($documentary->getDocumentaryVideoSources() != null) {
            $videoSources = [];

            foreach ($documentary->getDocumentaryVideoSources() as $documentaryVideoSource) {
                $videoSources[] = $documentaryVideoSource->getVideoSource()->getName();
            }

            $serialized['videoSources'] = $videoSources;
        }

        if ($movie->getVideoSource() != null) {
            $serialized['movie']['videoSource'] = [
                'id' => $movie->getVideoSource()->getId(),
                'name' => $movie->getVideoSource()->getName()
            ];
        }

        if ($movie->getVideoId() != null) {
            $serialized['movie']['videoId'] = $movie->getVideoId();
        }

        return $serialized;
    }

    /**
     * @param Documentary $documentary
     * @return array
     */
    private function serializeSeries(Documentary $documentary)
    {
        $series = $documentary->getSeries();

        $serialized = [
            'id' => $documentary->getId(),
            'type' => $documentary->getType(),
            'title' => $documentary->getTitle(),
            'slug' => $documentary->getSlug(),
            'storyline' => $documentary->getStoryline(),
            'summary' => $documentary->getSummary(),
            'status' => $documentary->getStatus(),
            'views' => $documentary->getViews(),
            'shortUrl' => $documentary->getShortUrl(),
            'featured' => $documentary->getFeatured(),
            'imdbId' => $documentary->getImdbId(),
            'yearFrom' => $documentary->getYearFrom(),
            'yearTo' => $documentary->getYearTo(),
            'poster' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $documentary->getPosterImagePath(),
            'wideImage' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $documentary->getWideImagePath(),
            'category' => [
                'id' => $documentary->getCategory()->getId(),
                'name' => $documentary->getCategory()->getName(),
                'slug' => $documentary->getCategory()->getSlug()
            ],
            'createdAt' => $documentary->getCreatedAt(),
            'updatedAt' => $documentary->getUpdatedAt()
        ];

        if ($documentary->getDocumentaryVideoSources() != null) {
            $videoSources = [];

            foreach ($documentary->getDocumentaryVideoSources() as $documentaryVideoSource) {
                $videoSources[] = $documentaryVideoSource->getVideoSource()->getName();
            }

            $serialized['videoSources'] = $videoSources;
        }

        if ($documentary->getAddedBy() != null) {
            $serialized['addedBy'] = [
                'username' => $documentary->getAddedBy()->getUsername()
            ];
        }

        $seasonsArray = [];
        foreach ($series->getSeasons() as $season) {

            $episodesArray = [];
            foreach ($season->getEpisodes() as $episode) {
                $episodesArray[] = [
                    'number' => $episode->getNumber(),
                    'title' => $episode->getTitle(),
                    'imdbId' => $episode->getImdbId(),
                    'storyline' => $episode->getStoryline(),
                    'summary' => $episode->getSummary(),
                    'length' => $episode->getLength(),
                    'year' => $episode->getYear(),
                    'videoSource' => $episode->getVideoSource()->getName(),
                    'videoId' => $episode->getVideoId(),
                    'thumbnail' => $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . $episode->getThumbnailImagePath(),
                ];
            }

            $seasonArray = [
                'number' => $season->getNumber(),
                'episodes' => $episodesArray
            ];

            $seasonsArray[] = $seasonArray;
        }

        $serialized['series'] = [
            'seasons' => $seasonsArray
        ];

        return $serialized;
    }
}