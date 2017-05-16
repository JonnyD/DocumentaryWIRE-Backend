<?php

namespace DW\CommentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DW\CommentBundle\Entity\Comment;
use DW\DocumentaryBundle\DataFixtures\ORM\DocumentaryFixtures;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\UserBundle\DataFixtures\ORM\UserFixtures;
use DW\UserBundle\Entity\User;

class CommentFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $documentary1 = $this->getDocumentary('Documentary 1');
        $documentary2 = $this->getDocumentary('Documentary 2');
        $documentary3 = $this->getDocumentary('Documentary 3');

        $user1 = $this->getUser('user1');
        $user2 = $this->getUser('user2');
        $user3 = $this->getUser('user3');

        $comment1 = $this->createComment($documentary1, $user1, 'This is a comment 1');
        $comment2 = $this->createComment($documentary1, $user2, 'This is a comment 2');
        $comment3 = $this->createComment($documentary2, $user3, 'This is a comment 3');
        $comment4 = $this->createComment($documentary2, $user1, 'This is a comment 4');
        $comment5 = $this->createComment($documentary3, $user2, 'This is a comment 5');
        $comment6 = $this->createComment($documentary3, $user3, 'This is a comment 6');

        $manager->persist($comment1);
        $manager->persist($comment2);
        $manager->persist($comment3);
        $manager->persist($comment4);
        $manager->persist($comment5);
        $manager->persist($comment6);
        $manager->flush();

        $this->createReference($comment1);
        $this->createReference($comment2);
        $this->createReference($comment3);
        $this->createReference($comment4);
        $this->createReference($comment5);
        $this->createReference($comment6);
    }

    /**
     * @param Documentary $documentary
     * @param User $user
     * @param string $commentText
     * @return Comment
     */
    private function createComment(Documentary $documentary, User $user, string $commentText)
    {
        $comment = new Comment();
        $comment->setDocumentary($documentary);
        $comment->setUser($user);
        $comment->setComment($commentText);
        return $comment;
    }

    /**
     * @param Comment $comment
     */
    private function createReference(Comment $comment)
    {
        $this->addReference('comment.'.$comment->getComment(), $comment);
    }

    /**
     * @param string $title
     * @return Documentary
     */
    private function getDocumentary(string $title)
    {
        return $this->getReference('documentary.'.$title);
    }

    /**
     * @param string $username
     * @return User
     */
    private function getUser(string $username)
    {
        return $this->getReference('user.'.$username);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            DocumentaryFixtures::class,
            UserFixtures::class
        ];
    }
}