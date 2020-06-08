<?php

namespace App\Controller;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Episode;
use App\Entity\Movie;
use App\Entity\Poster;
use App\Entity\Series;
use App\Entity\User;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\DocumentaryType;
use App\Enum\Featured;
use App\Enum\IsParent;
use App\Enum\Order;
use App\Form\AdminDocumentaryForm;
use App\Form\DocumentaryEpisodeForm;
use App\Form\DocumentaryMovieForm;
use App\Form\DocumentarySeriesForm;
use App\Form\SeriesForm;
use App\Form\MovieForm;
use App\Hydrator\EpisodeHydrator;
use App\Hydrator\MovieHydrator;
use App\Hydrator\SeriesHydrator;
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
                if (!DocumentaryStatus::hasStatus($status)) {
                    return $this->createApiResponse('Status does not exist', 404);
                }
                $criteria->setStatus($status);
            }
        }

        if (!$isRoleAdmin) {
            $criteria->setStatus(DocumentaryStatus::PUBLISH);
        }

        $featured = $request->query->get('featured');
        if (isset($featured)) {
            if (!Featured::hasStatus($featured)) {
                return $this->createApiResponse('Featured status does not exist', 404);
            }
            $criteria->setFeatured($featured);
        }

        $type = $request->query->get('type');
        if (isset($type)) {
            if (!DocumentaryType::hasType($type)) {
                return $this->createApiResponse('Type does not exist', 404);
            }
            $criteria->setType($type);
        }

        $categorySlug = $request->query->get('category');
        if (isset($categorySlug)) {
            $category = $this->categoryService->getCategoryBySlug($categorySlug);
            $criteria->setCategory($category);
        }

        $isParent = $request->query->get('isParent');
        if (isset($isParent)) {
            if (!IsParent::hasStatus($isParent)) {
                return $this->createApiResponse('IsParent status does not exist', 404);
            }
            if ($isRoleAdmin) {
                $criteria->setIsParent($isParent);
            } else {
                $criteria->setIsParent(IsParent::YES);
            }
        } else {
            $criteria->setIsParent(IsParent::YES);
        }

        $yearFrom = $request->query->get('year');
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
            $orderBy = $exploded[0];
            $order = $exploded[1];

            $hasOrderBy = DocumentaryOrderBy::hasOrderBy($orderBy);
            if (!$hasOrderBy) {
                return $this->createApiResponse('Order by ' . $orderBy . ' does not exist', 404);
            }

            $sort = [$orderBy => $order];
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

        $items = (array)$pagerfanta->getCurrentPageResults();

        $serialized = [];
        /** @var Documentary $item */
        foreach ($items as $item) {
            if ($item->isMovie()) {
                $movieHydrator = new MovieHydrator($item, $this->request);
                $serialized[] = $movieHydrator->toArray();
            } else if ($item->isSeries()) {
                $seriesHydrator = new SeriesHydrator($item, $this->request);
                $serialized[] = $seriesHydrator->toArray();
            } else if ($item->isEpisode()) {
                $episodeHydrator = new EpisodeHydrator($item, $this->request);
                $serialized[] = $episodeHydrator->toArray();
            } else {
                throw new \Exception();
            }
        }

        $data = [
            'items' => $serialized,
            'count_results' => $pagerfanta->getNbResults(),
            'current_page' => $pagerfanta->getCurrentPage(),
            'number_of_pages' => $pagerfanta->getNbPages(),
            'next' => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev' => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate' => $pagerfanta->haveToPaginate(),
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
            $movieHydrator = new MovieHydrator($documentary, $this->request);
            $serialized = $movieHydrator->toArray();
        } else if ($documentary->isSeries()) {
            $seriesHydrator = new SeriesHydrator($documentary, $this->request);
            $serialized = $seriesHydrator->toArray();
        }

        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Post("/documentary/movie", name="create_movie_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createMovieDocumentaryAction(Request $request)
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
                
                $this->documentaryService->save($documentary);

                $this->categoryService->updateDocumentaryCountForCategory($documentary->getCategory());

                $movieHydrator = new MovieHydrator($documentary, $this->request);
                $serialized = $movieHydrator->toArray();
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
        $series = new Series();

        $documentary = new Documentary();
        $documentary->setSeries($series);
        $documentary->setType(DocumentaryType::SERIES);
        $documentary->setAddedBy($this->getLoggedInUser());
        $documentary->setStatus(DocumentaryStatus::PENDING);
        $documentary->setIsParent(IsParent::YES);

        $form = $this->createForm(DocumentarySeriesForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapImages($documentary, $data);

                $this->documentaryService->save($documentary);

                $this->categoryService->updateDocumentaryCountForCategory($documentary->getCategory());

                $seriesHydrator = new SeriesHydrator($documentary, $this->request);
                $serialized = $seriesHydrator->toArray();
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400,);
            }
        }
    }

    /**
     * @FOSRest\Post("/documentary/episode", name="create_episode_documentary", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createEpisodeAction(Request $request)
    {
        $episode = new Episode();

        $documentary = new Documentary();
        $documentary->setEpisode($episode);
        $documentary->setType(DocumentaryType::EPISODE);
        $documentary->setAddedBy($this->getLoggedInUser());
        $documentary->setStatus(DocumentaryStatus::PENDING);

        $form = $this->createForm(DocumentaryEpisodeForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($documentary->getParent() === null) {
                    return $this->createApiResponse('You must specify a parent', 401);
                }

                $documentary = $this->imageService->mapImages($documentary, $data);

                $this->documentaryService->save($documentary);

                $serialized = ['test'=>'test'];
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

        if ($documentary === null) {
            return new AccessDeniedException();
        }

        if (!$documentary->isMovie()) {
            //@todo

            throw new \Exception();
        }

        $oldCategory = $documentary->getCategory();

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
                $this->categoryService->updateDocumentaryCountForCategories(
                    $newCategory, $oldCategory, $documentary);

                $movieHydrator = new MovieHydrator($documentary, $this->request);
                $serialized = $movieHydrator->toArray();
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

        $oldCategory = $documentary->getCategory();

        $form = $this->createForm(DocumentarySeriesForm::class, $documentary);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $documentary = $this->imageService->mapImages($documentary, $data);

                $this->documentaryService->save($documentary);

                $newCategory = $documentary->getCategory();
                $this->categoryService->updateDocumentaryCountForCategories(
                    $newCategory, $oldCategory, $documentary);

                $seriesHydrator = new SeriesHydrator($documentary, $this->request);
                $serialized = $seriesHydrator->toArray();
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
            }
        }
    }

    /**
     * @FOSRest\Put("/documentary/convert-to-series/{id}", name="convert_to_series_documentary", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function convertToSeriesAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $documentary = $this->documentaryService->getDocumentaryById($id);

        if ($documentary->isSeries()) {
            //@TODO throw already is a series
            return;
        }

        $movie = $documentary->getMovie();

        $series = new Series();
        $series->setDocumentary($documentary);

        $documentary->setSeries($series);
        $documentary->setMovie(null);
        $documentary->setType(DocumentaryType::SERIES);

        $this->documentaryService->save($documentary);

        $this->documentaryService->removeMovie($movie);

        $seriesHydrator = new SeriesHydrator($documentary, $this->request);
        $serialise = $seriesHydrator->toArray();
        return $this->createApiResponse($serialise, 200);

    }

    /**
     * @param array $data
     * @param Documentary $documentary
     * @return Documentary
     */
    public function mapArrayToObject(array $data, Documentary $documentary)
    {
        //@TODO - is this neccrssary?
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
}