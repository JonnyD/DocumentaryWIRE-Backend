<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
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
     * @param User $user
     */
    public function encodePassword(User $user)
    {
        $encodedPass = $this->encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($encodedPass);

        $this->userRepository->save($user);
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

        $this->userRepository->save($user);

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

        $this->userRepository->save($user);

        return $resetKey;
    }

    /**
     * @param User $user
     */
    public function resetPassword(User $user)
    {
        $this->userRepository->save($user);
    }

    /**
     * @param User $user
     * @param bool $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(User $user, $sync = true)
    {
        $this->userRepository->save($user, $sync);
    }

    public function flush()
    {
        $this->userRepository->flush();
    }
}