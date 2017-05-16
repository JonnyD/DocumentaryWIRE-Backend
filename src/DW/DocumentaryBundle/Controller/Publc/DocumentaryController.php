<?php

namespace DW\DocumentaryBundle\Controller\Publc;

use DW\BaseBundle\Enum\Order;
use DW\CommentBundle\Criteria\CommentCriteria;
use DW\CommentBundle\Entity\Comment;
use DW\CommentBundle\Form\GuestComment;
use DW\CommentBundle\Form\UserComment;
use DW\CommentBundle\Service\CommentService;
use DW\CommentBundle\Enum\OrderBy as CommentOrderBy;
use DW\DocumentaryBundle\Criteria\DocumentaryCriteria;
use DW\DocumentaryBundle\Enum\DocumentaryStatus;
use DW\DocumentaryBundle\Enum\OrderBy;
use DW\DocumentaryBundle\Service\DocumentaryService;
use DW\UserBundle\Service\SecurityService;
use DW\UserBundle\Service\UserService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentaryController extends Controller
{
    /**
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function showAction(Request $request, string $slug)
    {
        $documentaryService = $this->getDocumentaryService();
        $documentary = $documentaryService->getDocumentaryBySlug($slug);

        if ($documentary == null) {
            $this->createNotFoundException();
        }

        $documentary->incrementViews();
        $documentaryService->save($documentary);

        $embedCode = $documentaryService->getEmbedCode(
            $documentary->getVideoSource(),
            $documentary->getVideoId(),
            600, 400, true);

        $commentService = $this->getCommentService();
        $comments = $commentService->getCommentsByDocumentary($documentary);

        $categoryDocumentaries = $documentary->getCategory()->getDocumentaries()->toArray();
        shuffle($categoryDocumentaries);
        $randomDocumentaries = array_slice($categoryDocumentaries, 0, 4);

        $comment = new Comment();
        $form = $this->createForm(UserComment::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $securityService = $this->getSecurityService();
            if (!$securityService->isLoggedIn()) {
                $form->addError(new FormError("You must be logged in"));
            }
            if ($form->isValid()) {
                $comment->setDocumentary($documentary);
                $comment->setUser($this->getSecurityService()->getLoggedInUser());
                $commentService->save($comment);

                $documentary->incrementCommentCount();
                $documentaryService->save($documentary);

                return $this->redirectToRoute('dw.show_documentary', [
                    'slug' => $documentary->getSlug()
                ]);
            }
        }

        return $this->render('DocumentaryBundle:Publc:show.html.twig', [
            'embedCode' => $embedCode,
            'documentary' => $documentary,
            'randomDocumentaries' => $randomDocumentaries,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function browseAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISH);
        $criteria->setSort([
            OrderBy::CREATED_AT => Order::DESC
        ]);

        $documentaryService = $this->getDocumentaryService();
        $qb = $documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        return $this->render('DocumentaryBundle:Publc:browse.html.twig', [
            'documentaries' => $pagerfanta
        ]);
    }

    public function listAction()
    {
        $documentaryService = $this->getDocumentaryService();
        $documentaries = $documentaryService->getPublishedDocumentaries();

        return $this->render('DocumentaryBundle:Publc:list.html.twig', [
            'documentaries' => $documentaries
        ]);
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get("dw.documentary_service");
    }

    /**
     * @return CommentService
     */
    private function getCommentService()
    {
        return $this->get("dw.comment_service");
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->get('dw.user_service');
    }

    /**
     * @return SecurityService
     */
    private function getSecurityService()
    {
        return $this->get('dw.security_service');
    }
}