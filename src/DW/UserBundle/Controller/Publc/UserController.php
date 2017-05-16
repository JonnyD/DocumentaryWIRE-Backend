<?php

namespace DW\UserBundle\Controller\Publc;

use DW\ActivityBundle\Service\ActivityService;
use DW\CommentBundle\Service\CommentService;
use DW\UserBundle\Service\SecurityService;
use DW\UserBundle\Service\UserService;
use DW\WatchlistBundle\Service\WatchlistService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * @param string $username
     * @return Response|NotFoundHttpException
     */
    public function profileAction(string $username)
    {
        $userService = $this->getUserService();
        $user = $userService->getUserByUsername($username);

        if ($user == null) {
            return $this->createNotFoundException();
        }

        $securityService = $this->getSecurityService();
        $isUser = $securityService->getLoggedInUser() == $user;

        $commentService = $this->getCommentService();
        $comments = $commentService->getCommentsByUser($user);

        $watchlistService = $this->getWatchlistService();
        $watchlisted = $watchlistService->getWatchlistedByUser($user);

        return $this->render('UserBundle:Publc/User:profile.html.twig', [
            'user' => $user,
            'isUser' => $isUser,
            'comments' => $comments,
            'watchlisted' => $watchlisted
        ]);
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

    /**
     * @return CommentService
     */
    private function getCommentService()
    {
        return $this->get('dw.comment_service');
    }

    /**
     * @return WatchlistService
     */
    private function getWatchlistService()
    {
        return $this->get('dw.watchlist_service');
    }
}