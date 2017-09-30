<?php

namespace DW\CommentBundle\Controller\Admin;

use DW\CommentBundle\Service\CommentService;

class CommentController
{
    public function listAction()
    {
        $commentService = $this->getCommentService();
        $comments = $commentService->getAllComments();

        return $this->render('CommentBundle:Admin:list.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @return CommentService
     */
    private function getCommentService()
    {
        return $this->get('dw.comment_service');
    }
}
