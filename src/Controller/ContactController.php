<?php

namespace App\Controller;

use App\Criteria\ContactCriteria;
use App\Entity\Contact;
use App\Enum\ContactOrderBy;
use App\Enum\Order;
use App\Form\ContactForm;
use App\Hydrator\ContactHydrator;
use App\Service\ContactService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends BaseController implements ClassResourceInterface
{
    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @param ContactService $contactService
     */
    public function __construct(
        ContactService $contactService
    )
    {
        $this->contactService = $contactService;
    }

    /**
     * @FOSRest\Get("/contact", name="get_contact_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @throws \Doctrine\ORM\ORMException
     */
    public function listAction(Request $request)
    {
        $criteria = new ContactCriteria();
        $criteria->setSort([
            ContactOrderBy::CREATED_AT => Order::DESC
        ]);

        $page = $request->query->get('page', 1);

        $qb = $this->contactService->getContactsByQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = [];
        foreach ($items as $item) {
            $contactHydrator = new ContactHydrator($item);
            $serialized[] = $contactHydrator->toArray();
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
     * @FOSRest\Get("/contact/{id}", name="get_contact", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return string
     */
    public function getCommentAction(int $id)
    {
        $contact = $this->contactService->getContactById($id);

        $contactHydrator = new ContactHydrator($contact);
        $serialized = $contactHydrator->toArray();

        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Post("/contact", name="create_contact", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function contactAction(Request $request)
    {
        $contact = new Contact();

        $form = $this->createForm(ContactForm::class, $contact);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isValid()) {
                $subject = $data["subject"];
                $email = $data["emailAddress"];
                $message = $data["message"];

                $contact = new Contact();
                $contact->setSubject($subject);
                $contact->setEmailAddress($email);
                $contact->setMessage($message);

                $this->contactService->save($contact);

                $contactHydrator = new ContactHydrator($contact);
                $serializedContact = $contactHydrator->toArray();
                return $this->createApiResponse($serializedContact, 200);

            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 200);
            }
        }

    }
}