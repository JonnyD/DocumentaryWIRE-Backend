<?php

namespace DW\CommentBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DW\BaseBundle\Enum\Order;
use DW\CommentBundle\Criteria\CommentCriteria;
use DW\CommentBundle\Entity\Comment;
use DW\CommentBundle\Enum\CommentStatus;
use DW\CommentBundle\Enum\OrderBy;
use DW\CommentBundle\Event\CommentEvent;
use DW\CommentBundle\Event\CommentEvents;
use DW\CommentBundle\Repository\CommentRepository;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentService
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param CommentRepository $commentRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        CommentRepository $commentRepository,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->commentRepository = $commentRepository;
        $this->eventDispatcher =$eventDispatcher;
    }

    /**
     * @param CommentCriteria $criteria
     * @return ArrayCollection|Comment[]
     */
    public function getCommentsByCriteria(CommentCriteria $criteria)
    {
        return $this->commentRepository->findAllByCriteria($criteria);
    }

    /**
     * @param Documentary $documentary
     * @return ArrayCollection|Comment[]
     */
    public function getCommentsByDocumentary(Documentary $documentary)
    {
        $criteria = new CommentCriteria();
        $criteria->setStatus(CommentStatus::PUBLISH);
        $criteria->setDocumentary($documentary);
        $criteria->setSort([
            OrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->commentRepository->findAllByCriteria($criteria);
    }

    /**
     * @param User $user
     * @return ArrayCollection|Comment[]
     */
    public function getCommentsByUser(User $user)
    {
        $criteria = new CommentCriteria();
        $criteria->setStatus(CommentStatus::PUBLISH);
        $criteria->setUser($user);
        $criteria->setSort([
            OrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->commentRepository->findAllByCriteria($criteria);
    }

    /**
     * @param User $user
     */
    public function mapCommentsToUser(User $user)
    {
        $comments = $this->commentRepository->findBy([
            'email' => $user->getEmail()
        ]);

        foreach ($comments as $comment) {
            $comment->setUser($user);
            $this->commentRepository->save($comment, false);
        }

        $this->commentRepository->flush();
    }

    /**
     * @param Comment $comment
     * @param bool $sync
     */
    public function save(Comment $comment, bool $sync = true)
    {
        $this->commentRepository->save($comment, $sync);
        $this->eventDispatcher->dispatch(CommentEvents::COMMENT_ADDED, new CommentEvent($comment));
    }
}