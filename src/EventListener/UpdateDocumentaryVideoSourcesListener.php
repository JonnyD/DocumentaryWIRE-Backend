<?php

namespace App\EventListener;

use App\Entity\DocumentaryVideoSource;
use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Event\DocumentaryEvent;
use App\Event\DocumentaryEvents;
use App\Service\CategoryService;
use App\Service\DocumentaryService;
use App\Service\DocumentaryVideoSourceService;
use App\Service\UserService;
use App\Service\VideoSourceService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateDocumentaryVideoSourcesListener implements EventSubscriberInterface
{
    /**
     * @var DocumentaryVideoSourceService
     */
    private $documentaryVideoSourceService;

    /**
     * @param DocumentaryVideoSourceService $documentaryVideoSourceService
     */
    public function __construct(
        DocumentaryVideoSourceService $documentaryVideoSourceService
    )
    {
        $this->documentaryVideoSourceService = $documentaryVideoSourceService;
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function onDocumentarySaved(DocumentaryEvent $documentaryEvent)
    {
        $documentary = $documentaryEvent->getDocumentary();

        if ($documentary->isMovie()) {
            //$this->documentaryVideoSourceService->addDocumentaryVideoSourcesFromMovieDocumentary()
        } else if ($documentary->isSeries()) {
            //$this->documentaryVideoSourceService->addDocumentaryVideoSourcesFroSeriesDocumentary();
        } else {
            throw new \Exception("Does not know type");
        }
    }
}