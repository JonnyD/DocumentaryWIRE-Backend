<?php

namespace App\Controller;

use App\Criteria\CommentCriteria;
use App\Entity\Comment;
use App\Enum\CommentOrderBy;
use App\Enum\CommentStatus;
use App\Enum\Order;
use App\Form\CommentForm;
use App\Hydrator\CommentHydrator;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class CommentController extends BaseController implements ClassResourceInterface
{
    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param CommentService $commentService
     * @param DocumentaryService $documentaryService
     * @param UserService $userService
     */
    public function __construct(
        CommentService $commentService,
        DocumentaryService $documentaryService,
        UserService $userService)
    {
        $this->commentService = $commentService;
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
    }

    /**
     * @FOSRest\Get("/comment", name="get_comment_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        $documentaryId = $request->query->get('documentary');

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin && !isset($documentaryId)) {
            return $this->createApiResponse('Documentary ID is required', 400);
        }

        $criteria = new CommentCriteria();

        if (isset($documentaryId)) {
            $documentary = $this->documentaryService->getDocumentaryById($documentaryId);
            if (!$documentary) {
                return $this->createApiResponse('Documentary cannot be found', 404);
            }
            $criteria->setDocumentary($documentary);
        }

        $status = $request->query->get('status');
        if (isset($status)) {
            $hasStatus = CommentStatus::hasStatus($status);
            if (!$hasStatus) {
                return $this->createApiResponse('Status ' . $status . ' does not exist', 404);
            }
            if (!$isRoleAdmin) {
                return $this->createApiResponse('Only admins can change status', 400);
            } else {
                $criteria->setStatus($status);
            }
        } else {
            if (!$isRoleAdmin) {
                $criteria->setStatus(CommentStatus::PUBLISHED);
            }
        }

        $email = $request->query->get('email');
        if (isset($email)) {
            $criteria->setEmail($email);
        }

        $userId = $request->query->get('user');
        if (isset($userId)) {
            $user = $this->userService->getUserById($userId);
            if (!$user) {
                return $this->createApiResponse('User cannot be found', 404);
            }
            $criteria->setUser($user);
        }

        $criteria->setSort([
            CommentOrderBy::CREATED_AT => Order::DESC
        ]);

        $page = $request->query->get('page', 1);

        $qb = $this->commentService->getCommentsByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $commentHydrator = new CommentHydrator($item, $this->isGranted('ROLE_ADMIN'));
            $serialized[] = $commentHydrator->toArray();
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
     * @FOSRest\Get("/comment/{id}", name="get_comment", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return Comment|null
     */
    public function getCommentAction(int $id)
    {
        $comment = $this->commentService->getCommentById($id);
        if (!$comment) {
            return $this->createApiResponse('Comment does not exist', 404);
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');

        if (($comment->isPending() || $comment->isDeleted()) && !$isRoleAdmin) {
            return $this->createApiResponse('Unauthorized to view this comment', 400);
        }

        $commentHydrator = new CommentHydrator($comment, $isRoleAdmin);
        $serialized = $commentHydrator->toArray();

        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Patch("/comment/{id}", name="partial_update_comment", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editCommentAction(int $id, Request $request)
    {
        $isAuthorized = false;

        /** @var Comment $comment */
        $comment = $this->commentService->getCommentById($id);
        if ($comment === null) {
            return $this->createApiResponse('Comment not found', 404);
        }

        $commentStatus = $comment->getStatus();

        if ($comment != null) {
            $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
            $commentUser = $comment->getUser();
            if (($this->isLoggedIn() && $commentUser->getId() == $this->getLoggedInUser()->getId())
                || $isRoleAdmin) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return $this->createApiResponse('Not authorized', 401);
        }

        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if (!$isRoleAdmin) {
                if ($data['status'] != $commentStatus) {
                    return $this->createApiResponse('Only admins can edit comment status', 401);
                }
            }

            if ($form->isValid()) {
                $this->commentService->save($comment);

                $commentHydrator = new CommentHydrator($comment, $this->isGranted('ROLE_ADMIN'));
                $serializedComment = $commentHydrator->toArray();
                return $this->createApiResponse($serializedComment, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
            }
        }
    }

    public function deleteCommentAction()
    {
        //@TODO
    }

    public function createCommentAction()
    {
        //@TODO
    }
}