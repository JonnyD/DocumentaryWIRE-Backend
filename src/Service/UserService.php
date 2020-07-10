<?php

namespace App\Service;

use App\Criteria\UserCriteria;
use App\Entity\User;
use App\Enum\Order;
use App\Enum\Sync;
use App\Enum\UserOrderBy;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserRepository $userRepository
     * @param EmailService $emailService
     * @param ActivityService $activityService
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserRepository $userRepository,
        EmailService $emailService,
        ActivityService $activityService,
        UserPasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
        $this->activityService = $activityService;
        $this->encoder = $encoder;
    }

    /**
     * @return User[]
     */
    public function getAllUsers()
    {
        return $this->userRepository->findAll();
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id)
    {
        return $this->userRepository->find($id);
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername(string $username)
    {
        return $this->userRepository->findOneBy([
            'username' => $username
        ]);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email)
    {
        return $this->userRepository->findOneBy([
            'email' => $email
        ]);
    }

    /**
     * @param User $user
     */
    public function encodePassword(User $user)
    {
        $encodedPass = $this->encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($encodedPass);

        $this->save($user);
    }

    /**
     * @param User $user
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    public function generateActivationKey(User $user)
    {
        $confirmationToken = sha1(mt_rand(10000,99999).time().$user->getEmail());
        $user->setConfirmationToken($confirmationToken);

        $this->save($user);

        return $confirmationToken;
    }

    /**
     * @param string $username
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    public function generatePasswordResetKey(string $username)
    {
        $user = $this->getUserByUsername($username);

        $resetKey = sha1(mt_rand(10000,99999).time().$user->getEmail());
        $user->setResetKey($resetKey);
        $user->setPasswordRequestedAt(new \DateTime());

        $this->save($user);

        return $resetKey;
    }

    /**
     * @param User $user
     */
    public function resetPassword(User $user)
    {
        //@TODO
        $this->save($user);
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getNewestUsers()
    {
        $criteria = new UserCriteria();
        $criteria->setSort([
            UserOrderBy::ACTIVATED_AT => Order::DESC
        ]);

        return $this->userRepository->findUsersByCriteria($criteria);
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getActiveUsers()
    {
        $criteria = new UserCriteria();
        $criteria->setSort([
            UserOrderBy::LAST_LOGIN => Order::DESC
        ]);

        return $this->userRepository->findUsersByCriteria($criteria);
    }


    /**
     * @param UserCriteria $criteria
     * @return QueryBuilder
     */
    public function getUsersByCriteriaQueryBuilder(UserCriteria $criteria)
    {
        return $this->userRepository->findUsersByCriteriaQueryBuilder($criteria);
    }

    /**
     * @param UserCriteria $criteria
     * @return User[]
     */
    public function getUsersByCriteria(UserCriteria $criteria)
    {
        return $this->userRepository->findUsersByCriteria($criteria);
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function confirmUser(User $user)
    {
        $user->setActivatedAt(new \DateTime());
        $user->setEnabled(true);

        //@TODO
        //$this->emailService->subscribe($user->getEmailCanonical());

        $this->save($user);

        //$this->activityService->addJoinedActivity($user);
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateLastLogin(User $user)
    {
        $user->setLastLogin(new \DateTime());
        $this->save($user);
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateWatchlistCountForUser(User $user)
    {
        $count = 0;

        $watchlists = $user->getWatchlists();
        foreach ($watchlists as $watchlist) {
            $count++;
        }

        $user->setWatchlistCount($count);
        $this->saveAndDontUpdateTimestamps($user);
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateCommentCountForUser(User $user)
    {
        $count = 0;

        $comments = $user->getComments();
        foreach ($comments as $comment) {
            if ($comment->isPublished()) {
                $count++;
            }
        }

        $user->setCommentCount($count);
        $this->saveAndDontUpdateTimestamps($user);
    }

    /**
     * @param User $user
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveAndDontUpdateTimestamps(User $user, string $sync = Sync::YES)
    {
        $this->userRepository->save($user, $sync);
    }

    /**
     * @param User $user
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(User $user, string $sync = Sync::YES)

    {
        if ($user->getCreatedAt() == null) {
            $user->setCreatedAt(new \DateTime());
        } else {
            $user->setUpdatedAt(new \DateTime());
        }

        $this->userRepository->save($user, $sync);
    }

    public function flush()
    {
        $this->userRepository->flush();
    }
}