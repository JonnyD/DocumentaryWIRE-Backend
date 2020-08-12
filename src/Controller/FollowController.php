<?php

namespace App\Controller;

use App\Criteria\FollowCriteria;
use App\Entity\Follow;
use App\Entity\User;
use App\Form\FollowForm;
use App\Hydrator\FollowHydrator;
use App\Service\FollowService;
use App\Service\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FollowController extends BaseController implements ClassResourceInterface
{
    /**
     * @var FollowService
     */
    private $followService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param FollowService $followService
     * @param UserService $userService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FollowService $followService,
        UserService $userService,
        TokenStorageInterface $tokenStorage)
    {
        $this->followService = $followService;
        $this->userService = $userService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @FOSRest\Get("/follow/{id}", name="get_follow", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse|null
     */
    public function getFollowAction(int $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $follow = $this->followService->getFollowById($id);
        if ($follow == null) {
            return $this->createApiResponse('Follow not found', 404);
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        $isOwner = $this->isLoggedIn() && $this->getLoggedInUser()->getId() === $follow->getUserFrom()->getId();
        if (!$isRoleAdmin && !$isOwner) {
            return $this->createApiResponse('Not authorized', 401);
        }

        $followHydrator = new FollowHydrator($follow);
        $serialized = $followHydrator->toArray();
        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Get("/follow/{userFromId}/{userToId}", name="get_follow_for_userTo_and_userTo", options={ "method_prefix" = false })
     *
     * @param int $userFromId
     * @param int $userToId
     * @return JsonResponse|null
     */
    public function getFollowForUserFromAndUserToAction(int $userFromId, int $userToId)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userFrom = $this->userService->getUserById($userFromId);
        if ($userFrom == null) {
            return $this->createApiResponse('User From not found', 404);
        }

        $userTo = $this->userService->getUserById($userToId);
        if ($userTo == null) {
            return $this->createApiResponse('User To not found', 404);
        }

        $follow = $this->followService->getFollowForUserFromAndUserTo($userFrom, $userTo);

        if ($follow == null) {
            return $this->createApiResponse('Follow not found', 404);
        }

        $followHydrator = new FollowHydrator($follow);
        $serialized = $followHydrator->toArray();
        return $this->createApiResponse($serialized, 200);

    }

    /**
     * @FOSRest\Post("/follow", name="create_follow", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFollowAction(Request $request)
    {
        $follow = new Follow();

        $form = $this->createForm(FollowForm::class, $follow); //@TODO
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            $userFromId = $data['userFrom'];
            if (!isset($userFromId)) {
                return $this->createApiResponse('UserFrom ID not found', 404);
            }
            $userFrom = $this->userService->getUserById($userFromId);
            if (!$userFrom) {
                return $this->createApiResponse('UserTo does not exist', 404);
            }

            $userToId = $data['userTo'];
            if (!isset($userToId)) {
                return $this->createApiResponse('UserTo ID not found');
            }
            $userTo = $this->userService->getUserById($userToId);
            if (!$userTo) {
                return $this->createApiResponse('UserTo does not exist', 404);
            }

            $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
            if (!$isRoleAdmin) {
                $loggedInUser = $this->getLoggedInUser();
                if ($userFrom->getId() != $loggedInUser->getId()) {
                    $form->addError(new FormError("Unauthorized"));
                }
            }

            $criteria = new FollowCriteria();
            $criteria->setFrom($userFrom);
            $criteria->setTo($userTo);

            $existingFollow = $this->followService->getFollowByCriteria($criteria);
            if ($existingFollow) {
                $form->addError(new FormError("Follow already exists"));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->followService->save($follow);

                $followHydrator = new FollowHydrator($follow);
                $follow = $followHydrator->toArray();
                return $this->createApiResponse($follow, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200,);
            }
        }
    }

    /**
     * @FOSRest\Get("/follow", name="get_follow_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $page = $request->query->get('page', 1);

        $follower = $request->query->get('follower');
        $following = $request->query->get('following');

        $criteria = new FollowCriteria();

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin && !isset($follower) && !isset($following)) {
            return $this->createApiResponse('You must set either a Follower or Following', 401);
        }

        if (isset($following)) {
            $user = $this->userService->getUserById($following);
            if (!$user) {
                return $this->createApiResponse('Following not found', 404);
            }
            $criteria->setFrom($user);
        }

        if (isset($follower)) {
            $user = $this->userService->getUserById($follower);
            if (!$user) {
                return $this->createApiResponse('Follower not found', 404);
            }
            $criteria->setTo($user);
        }

        $qb = $this->followService->getFollowsByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(50);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $loggedInUser = $this->getLoggedInUser();

        $serialized = [];
        foreach ($items as $item) {
            $follow = null;

            if (isset($follower)) {
                $follow = $this->followService->getFollowForUserFromAndUserTo($loggedInUser, $item->getUserFrom());
            } else if (isset($following)) {
                $follow = $this->followService->getFollowForUserFromAndUserTo($loggedInUser, $item->getUserTo());
            }
            $isFollowing = $follow != null;

            $followHydrator = new FollowHydrator($item);
            $followArray = $followHydrator->toArray();
            $followArray['isFollowing'] = $isFollowing;

            $serialized[] = $followArray;
        }

        $data = [
            'items'             => $serialized,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return $this->createApiResponse($data, 200);
    }

    /**
     * @FOSRest\Delete("/follow/{id}", name="delete_follow", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse|null
     */
    public function removeFollowAction(int $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->isLoggedIn()) {
            return $this->createApiResponse('Not authorized', 401);
        }

        $follow = $this->followService->getFollowById($id);
        if (!$follow) {
            return $this->createApiResponse('Follow does not exist', 404);
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        $loggedInUser = $this->getLoggedInUser();
        $userFrom = $follow->getUserFrom();
        $userTo = $follow->getUserTo();

        $isLoggedInUserEqualsUserFrom = $loggedInUser->getId() === $userFrom->getId();
        if (!$isLoggedInUserEqualsUserFrom && !$isRoleAdmin) {
            return $this->createApiResponse('You are not allowed to change this follow', 401);
        }

        if ($isLoggedInUserEqualsUserFrom || $isRoleAdmin) {
            $this->followService->remove($follow);
        }

        return $this->createApiResponse("Deleted", 200);
    }
}