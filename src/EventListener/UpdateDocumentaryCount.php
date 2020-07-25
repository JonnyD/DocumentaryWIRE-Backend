<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Event\DocumentaryEvent;
use App\Event\DocumentaryEvents;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateDocumentaryCount implements EventSubscriberInterface
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @param CategoryService $categoryService
     */
    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            DocumentaryEvents::DOCUMENTARY_SAVED => "onDocumentarySaved"
        );
    }

    /**
     * @param DocumentaryEvent $documentaryEvent
     */
    public function onDocumentarySaved(DocumentaryEvent $documentaryEvent)
    {
        $documentary = $documentaryEvent->getDocumentary();
        $oldCategory = $documentary->getOldCategory();
        $newCategory = $documentary->getCategory();

        $this->categoryService->updateDocumentaryCountForCategories($newCategory, $oldCategory, $documentary);
    }
}