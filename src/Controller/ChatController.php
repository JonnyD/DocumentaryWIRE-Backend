<?php

namespace App\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Iflylabs\iFlyChat;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class ChatController extends BaseController implements ClassResourceInterface
{
    private $appId  = '5f75b938-7f51-4e1e-a14d-4c7c32d842be';
    private $apiKey = 'gbIwOrtca_DCecKJlFK7XVKt2-rn6IuXW5J785pfqYsW87107';

    /**
     * @var Request
     */
    private $request;

    /**
     * ChatController constructor
     * @param RequestStack $requestStack
     */
    public function __construct(
        RequestStack $requestStack
    )
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/chat", name="get_chat", options={ "method_prefix" = false })
     *
     * @return string
     */
    public function chatAction()
    {
        $iflychat = new iFlyChat($this->appId, $this->apiKey);

        $loggedInUser = $this->getLoggedInUser();

        if ($loggedInUser != null) {
            //$avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $loggedInUser->getAvatar();

            $user = array(
                'user_name' => $loggedInUser->getUsername(), // string(required)
                'user_id' => $loggedInUser->getId(), // string (required)
                'is_admin' => TRUE, // boolean (optional)
                'user_avatar_url' => 'fdfg', // string (optional)
                'user_profile_url' => 'user-profile-link', // string (optional)
            );
            $iflychat->setUser($user);
        }

        $iflychatCode = $iflychat->getHtmlCode();

        return $this->createApiResponse($iflychatCode, 200);
    }
}