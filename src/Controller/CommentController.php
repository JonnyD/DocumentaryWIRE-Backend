<?php

namespace App\Controller;

use App\Criteria\CommentCriteria;
use App\Entity\Comment;
use App\Enum\CommentOrderBy;
use App\Enum\Order;
use App\Form\CommentForm;
use App\Service\CommentService;
use App\Service\DocumentaryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class CommentController extends AbstractFOSRestController implements ClassResourceInterface
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
     * @param CommentService $commentService
     * @param DocumentaryService $documentaryService
     */
    public function __construct(
        CommentService $commentService,
        DocumentaryService $documentaryService)
    {
        $this->commentService = $commentService;
        $this->documentaryService = $documentaryService;
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
            throw new AccessDeniedException();
        }

        $criteria = new CommentCriteria();

        if (isset($documentaryId)) {
            $documentary = $this->documentaryService->getDocumentaryById($documentaryId);
            $criteria->setDocumentary($documentary);
        }

        $status = $request->query->get('status');
        if (isset($status)) {
            $criteria->setStatus($status);
        }

        $email = $request->query->get('email');
        if (isset($email)) {
            $criteria->setEmail($email);
        }

        $user = $request->query->get('user');
        if (isset($user)) {
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
            $serialized[] = $this->serialiseComment($item);
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
     * @FOSRest\Get("/comment/{id}", name="get_comment", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return Comment|null
     */
    public function getCommentAction(int $id)
    {
        $comment = $this->commentService->getCommentById($id);

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods: GET, POST',
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Max-Age: 86400',
            'Access-Control-Request-Headers' => [' X-Requested-With'],
        ];

        $serialized = $this->serialiseComment($comment);

        return new JsonResponse($serialized, 200, $headers);
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
        /** @var Comment $comment */
        $comment = $this->commentService->getCommentById($id);

        if ($comment === null) {
            return new AccessDeniedException();
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true)['resource'];
            $form->submit($data);

            if ($form->isValid()) {
                $this->commentService->save($comment);
                $serializedComment = $this->serialiseComment($comment);
                return new JsonResponse($serializedComment, 200, $headers);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return new JsonResponse($errors, 200, $headers);
            }
        }
    }

    /**
     * @param Comment $comment
     * @return array
     */
    private function serialiseComment(Comment $comment)
    {
        $serialized = [
            'id' => $comment->getId(),
            'commentText' => $comment->getCommentText(),
            'email' => $comment->getEmail(),
            'status' => $comment->getStatus(),
            'author' => $comment->getAuthor(),
            'createdAt' => $comment->getCreatedAt(),
            'updatedAt' => $comment->getUpdatedAt()
       ];

        if ($comment->getUser() != null) {
            $serialized['user'] = [
                'id' => $comment->getUser()->getId(),
                'username' => $comment->getUser()->getUsername()
            ];
        }

        if ($comment->getDocumentary() != null) {
            $serialized['documentary'] = [
                'id' => $comment->getDocumentary()->getId(),
                'title' => $comment->getDocumentary()->getTitle()
            ];
        }

        return $serialized;
    }
}