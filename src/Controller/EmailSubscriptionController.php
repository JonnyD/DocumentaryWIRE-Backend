<?php

namespace App\Controller;

use App\Criteria\EmailCriteria;
use App\Entity\Category;
use App\Entity\Email;
use App\Form\CategoryForm;
use App\Form\UnsubscribeEmailSubscriptionForm;
use App\Service\CategoryService;
use App\Service\EmailService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class EmailSubscriptionController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @param EmailService $emailService
     */
    public function __construct(
        EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @FOSRest\Get("/email", name="get_emails", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $page = $request->query->get('page', 1);

        $criteria = new EmailCriteria();

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $sort = [$exploded[0] => $exploded[1]];
            $criteria->setSort($sort);
        }

        $isSubscribed = $request->query->get('subscribed');
        if ($isSubscribed) {
            $criteria->setSubscribed($isSubscribed);
        }

        $email = $request->query->get('email');
        if ($email) {
            $criteria->setEmail($email);
        }

        $qb = $this->emailService->getEmailsByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(16);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = $this->serializeEmails($items);

        $data = [
            'items'             => $serialized,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];
        return new JsonResponse($data, 200, $headers);
    }

    /**
     * @FOSRest\Get("/email/unsubscribe", name="unsubscribe", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unsubscribeAction(Request $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ];

        $emailAddress = $request->query->get('email');
        if (!$emailAddress) {
            return new JsonResponse("Email not found", 400, $headers);
        } else {
            $existingEmail = $this->emailService->getEmailByEmailAddress($emailAddress);
            if (!$existingEmail) {
                return new JsonResponse("Email not found", 400, $headers);
            }
        }

        $subscriptionKey = $request->query->get('subscription_key');
        if (!$subscriptionKey) {
            return new JsonResponse("Subscription key not found", 400, $headers);
        } else {
            $existingEmail = $this->emailService->getEmailByEmailAddressAndSubscriptionKey($emailAddress, $subscriptionKey);
            if (!$existingEmail) {
                return new JsonResponse("Subscription key not found", 400, $headers);
            }
        }
        $unsubscribeData = [
            'email' => $emailAddress,
            'subscriptionKey' => $subscriptionKey
        ];

        $form = $this->createForm(UnsubscribeEmailSubscriptionForm::class, $unsubscribeData);
        $form->handleRequest($request);

        $form->submit($unsubscribeData);

        if ($form->isSubmitted() && $form->isValid()) {
            $unsubscribed = $this->emailService->unsubscribe($emailAddress);
            if ($unsubscribed) {
                return new JsonResponse("Email Unsubscribed", 200, $headers);
            } else {
                return new JsonResponse("An error occurred", 400, $headers);
            }
        } else {
            $errors = (string)$form->getErrors(true, false);
            return new JsonResponse($errors, 400, $headers);
        }
    }

    /**
     * @param $emails
     * @return array
     */
    private function serializeEmails($emails)
    {
        $serialized = [];

        foreach ($emails as $email) {
            $serialized[] = $this->serializeEmail($email);
        }

        return $serialized;
    }
    /**
     * @param Email $email
     * @return array
     */
    private function serializeEmail(Email $email)
    {
        $serializedEmail = [
            'id' => $email->getId(),
            'email' => $email->getEmail(),
            'subscribed' => $email->isSubscribed(),
            'subscriptionKey' => $email->getSubscriptionKey(),
            'createdAt' => $email->getCreatedAt(),
            'updatedAt' => $email->getUpdatedAt()
        ];

        return $serializedEmail;
    }
}