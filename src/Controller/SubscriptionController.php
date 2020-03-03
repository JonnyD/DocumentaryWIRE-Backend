<?php

namespace App\Controller;

use App\Criteria\SubscriptionCriteria;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\SubscriptionForm;
use App\Service\SubscriptionService;
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

class SubscriptionController extends BaseController implements ClassResourceInterface
{
    /**
     * @var SubscriptionService
     */
    private $subscriptionService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param SubscriptionService $subscriptionService
     * @param UserService $userService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        SubscriptionService $subscriptionService,
        UserService $userService,
        TokenStorageInterface $tokenStorage)
    {
        $this->subscriptionService = $subscriptionService;
        $this->userService = $userService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @FOSRest\Get("/subscription/{id}", name="get_subscription", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse|null
     */
    public function getSubscriptionAction(int $id)
    {
        $subscription = $this->subscriptionService->getSubscriptionById($id);
        if ($subscription == null) {
            return $this->createApiResponse('Subscription not found', 404);
        }

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        $isOwner = $this->isLoggedIn() && $this->getLoggedInUser()->getId() === $subscription->getUserFrom()->getId();
        if (!$isRoleAdmin && !$isOwner) {
            return $this->createApiResponse('Not authorized', 401);
        }

        $serialized = $this->serializeSubscription($subscription);
        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Post("/subscription", name="create_subscription", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createSubscriptionAction(Request $request)
    {
        $subscription = new Subscription();

        $form = $this->createForm(SubscriptionForm::class, $subscription);
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
                return $this->createApiResponse('UserFrom does not exist', 404);
            }

            $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
            if (!$isRoleAdmin) {
                $loggedInUser = $this->getLoggedInUser();
                if ($userFrom->getId() != $loggedInUser->getId()) {
                    $form->addError(new FormError("Unauthorized"));
                }
            }

            $criteria = new SubscriptionCriteria();
            $criteria->setFrom($userFrom);
            $criteria->setTo($userTo);

            $existingSubscription = $this->subscriptionService->getSubscriptionByCriteria($criteria);
            if ($existingSubscription) {
                $form->addError(new FormError("Subscription already exists"));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->subscriptionService->save($subscription);
                $subscription = $this->serializeSubscription($subscription);
                return $this->createApiResponse($subscription, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200,);
            }
        }
    }

    /**
     * @FOSRest\Get("/subscription", name="get_subscription_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $userFromId = $request->query->get('from');
        $userToId = $request->query->get('to');

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin) {
            if (!isset($userFromId)) {
                return $this->createApiResponse('You must specify User From ID', 404);
            }
        }

        $criteria = new SubscriptionCriteria();

        $loggedInUser = $this->getLoggedInUser();

        if (isset($userFromId)) {
            $user = $this->userService->getUserById($userFromId);

            $userEqualToLoggedInUser = $loggedInUser->getId() === $user->getId();
            if ($userEqualToLoggedInUser || $isRoleAdmin) {
                $criteria->setFrom($user);
            } else {
                return $this->createApiResponse('Not authorized', 401);
            }
        }

        if (isset($userToId)) {
            $user = $this->userService->getUserById($userToId);

            $userEqualToLoggedInUser = $loggedInUser->getId() === $user->getId();
            if ($userEqualToLoggedInUser || $isRoleAdmin) {
                $criteria->setTo($user);
            } else {
                return $this->createApiResponse('Not authorized', 401);
            }
        }

        $qb = $this->subscriptionService->getSubscriptionsByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(50);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $serialized[] = $this->serializeSubscription($item);
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
     * @FOSRest\Delete("/subscription/{id}", name="delete_subscription", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse|null
     */
    public function removeSubscriptionAction(int $id)
    {
        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');

        $subscription = $this->subscriptionService->getSubscriptionById($id);

        $loggedInUser = $this->getLoggedInUser();
        $userFrom = $subscription->getUserFrom();

        $isLoggedInUserEqualsUserFrom = $loggedInUser->getId() === $userFrom->getId();
        if ($isLoggedInUserEqualsUserFrom  || $isRoleAdmin) {
            $this->subscriptionService->remove($subscription);
        }

        return $this->createApiResponse("Deleted", 200);
    }

    /**
     * @param Subscription $subscription
     * @return array
     */
    private function serializeSubscription(Subscription $subscription)
    {
        return [
            'id' => $subscription->getId(),
            'userFrom' => [
                'id' => $subscription->getUserFrom()->getId(),
                'username' => $subscription->getUserFrom()->getUsername()
            ],
            'userTo' => [
                'id' => $subscription->getUserTo()->getId(),
                'username' => $subscription->getUserTo()->getUsername()
            ],
            'createdAt' => $subscription->getCreatedAt(),
            'updatedAt' => $subscription->getUpdatedAt()
        ];
    }
}