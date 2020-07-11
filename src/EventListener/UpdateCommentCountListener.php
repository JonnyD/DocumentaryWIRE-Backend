<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCommentCountListener implements EventSubscriberInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param DocumentaryService $documentaryService
     * @param UserService $userService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        UserService $userService
    )
    {
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            CommentEvents::COMMENT_CREATED => "onCommentCreated"
        );
    }

    public function onCommentCreated(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();

        $documentary = $comment->getDocumentary();
        $user = $comment->getUser();

        $this->documentaryService->updateCommentCountForDocumentary($documentary);
        $this->userService->updateCommentCountForUser($user);
    }
}