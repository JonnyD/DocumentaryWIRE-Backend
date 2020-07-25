<?php

namespace App\Controller;

use App\Service\UserService;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Iflylabs\iFlyChat;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class ChatController extends BaseController implements ClassResourceInterface
{
    private $appId  = 'af97346d-eeb4-49ea-b1e5-c4084f1f46d4';
    private $apiKey = 'BeVYDxmH39--CYyyYrstIzyPqQUFu8Xmg1o0LoEssikW87106';

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param UserService $userService
     * @param UserManagerInterface $userManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        UserService $userService,
        UserManagerInterface $userManager,
        RequestStack $requestStack
    )
    {
        $this->userService = $userService;
        $this->userManager = $userManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @FOSRest\Get("/chat/chat", name="get_chat", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return string
     */
    public function chatAction(Request $request)
    {
        $iflychat = new iFlyChat($this->appId, $this->apiKey);

        $loggedInUser = $this->getLoggedInUser();

        if ($loggedInUser != null) {
            //$avatar = $this->request->getScheme() .'://' . $this->request->getHttpHost() . $this->request->getBasePath() . '/uploads/avatar/' . $loggedInUser->getAvatar();

            $user = array(
                'user_name' => $loggedInUser->getUsername(), // string(required)
                'user_id' => (string)$loggedInUser->getId(), // string (required)
                //'is_admin' => TRUE, // boolean (optional)
                //'user_avatar_url' => 'fdfg', // string (optional)
                //'user_profile_url' => 'user-profile-link', // string (optional)
            );
            $iflychat->setUser($user);
        }

        $iflychatCode = $iflychat->getHtmlCode();
        //$iflychatCode = str_replace('<script>', '', $iflychatCode);
        //$iflychatCode = str_replace('</script>', '', $iflychatCode);
        echo $iflychatCode; die();

        $jsonResponse = $this->createApiResponse($iflychatCode, 200);
        $callback = $request->get('callback');
        $jsonResponse->setCallback($callback);

        return $jsonResponse;
    }
}