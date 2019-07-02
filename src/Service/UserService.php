<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    public function generateActivationCode(User $user)
    {
        $generatedKey = sha1(mt_rand(10000,99999).time().$user->getEmail());
        $user->setActivationKey($generatedKey);

        $this->userRepository->save($user);

        return $generatedKey;
    }
}