<?php

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonForm;
use App\Hydrator\SeasonHydrator;
use App\Service\SeasonService;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class SeasonController extends BaseController implements ClassResourceInterface
{
    /**
     * @var SeasonService
     */
    private $seasonService;

    /**
     * @param SeasonService $seasonService
     */
    public function __construct(
        SeasonService $seasonService
    )
    {
        $this->seasonService = $seasonService;
    }

    /**
     * @FOSRest\Post("/season", name="create_season", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createSeasonAction(Request $request)
    {
        $season = new Season();

        $form = $this->createForm(SeasonForm::class, $season);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);


            if ($form->isSubmitted() && $form->isValid()) {
                $this->seasonService->save($season);

                $seasonHydrator = new SeasonHydrator($season);
                $serialized = $seasonHydrator->toArray();
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }
}