<?php

namespace App\Controller;

use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractFOSRestController
{
    /**
     * @param $data
     * @param int $statusCode
     * @return Response
     */
    protected function createApiResponse($data, $statusCode = 200)
    {
        $headers = [
            'Access-Control-Allow-Origin'=> '*'
        ];

        return new JsonResponse($data, $statusCode, $headers);
    }

    /**
     * @param $data
     * @param string $format
     * @return mixed
     */
    protected function serialize($data, $format = 'json')
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return SerializerBuilder::create()->build()->serialize($data, $format, $context);
    }

    /**
     * @return User
     */
    protected function getLoggedInUser()
    {
        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();

        if (is_string($loggedInUser)) {
            $loggedInUser = null;
        }

        return $loggedInUser;
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        if (is_string($this->getLoggedInUser())){
            return false;
        }
        return $this->getLoggedInUser()->isGranted("ROLE_USER");
    }
}